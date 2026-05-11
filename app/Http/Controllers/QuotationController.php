<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\QuotationItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class QuotationController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->isCompanyAdmin()) {
            $quotations = Quotation::where('user_id', $user->id)
                ->whereNull('parent_id')
                ->with(['client', 'revisions'])
                ->latest()
                ->paginate(10);
        } elseif ($user->isClientUser()) {
            $quotations = Quotation::where('client_id', $user->client_id)
                ->whereNull('parent_id')
                ->with(['client', 'revisions'])
                ->latest()
                ->paginate(10);
        } else {
            $quotations = collect([]); 
        }

        return view('quotations.index', compact('quotations'));
    }

    public function create(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isCompanyAdmin()) {
            abort(403, 'Only administrators can create quotations.');
        }

        $clients = $user->clients;
        $companyCustomFields = $this->getCompanyCustomFields($user, 'show_in_quotation');
        $existingCF = collect();

        $sourceQuotation = null;
        if ($request->has('source_id')) {
            $sourceQuotation = Quotation::with('items')->where('user_id', $user->id)->findOrFail($request->source_id);
            if ($sourceQuotation->isLocked()) {
                return redirect()->route('quotations.show', $sourceQuotation)->with('error', 'This quotation series is locked because a version has already been approved.');
            }
            $existingCF = collect($sourceQuotation->custom_fields ?? [])->keyBy('key');
        }

        return view('quotations.create', compact('clients', 'sourceQuotation', 'companyCustomFields', 'existingCF'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isCompanyAdmin()) {
            abort(403, 'Only administrators can create quotations.');
        }

        $request->merge([
            'quotation_number' => $request->quotation_number ?? 'QT-' . strtoupper(Str::random(8)),
        ]);

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'quotation_number' => 'required|string|unique:quotations,quotation_number',
            'quotation_date' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:quotation_date',
            'quotation_type' => 'required|string|in:regular,export,interstate',
            'place_of_supply' => 'nullable|string|size:2',
            'currency' => 'required|string|size:3',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.hsn_code' => 'nullable|string',
            'items.*.tax_rate' => 'nullable|numeric|in:0,5,12,18,28',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'custom_fields' => 'nullable|array',
            'custom_fields.*.key' => 'required|string|max:100',
            'custom_fields.*.value' => 'nullable|string|max:255',
            'client_notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'parent_id' => 'nullable|exists:quotations,id',
            'revision_number' => 'nullable|integer',
        ]);

        $client = Client::findOrFail($validated['client_id']);
        
        if ($client->user_id !== Auth::id()) {
            abort(403);
        }

        $subtotal = 0;
        $cgst = 0;
        $sgst = 0;
        $igst = 0;
        $quotationType = $validated['quotation_type'];

        foreach ($validated['items'] as $item) {
            $itemTotal = $item['quantity'] * $item['unit_price'];
            $subtotal += $itemTotal;
            
            $taxRate = $item['tax_rate'] ?? 0;
            $taxAmount = $itemTotal * ($taxRate / 100);

            if ($quotationType === 'export' || $quotationType === 'interstate') {
                 $igst += $taxAmount; 
            } else {
                 $cgst += $taxAmount / 2;
                 $sgst += $taxAmount / 2;
            }
        }

        $total = $subtotal + $cgst + $sgst + $igst;

        $revisionNumber = 0;
        if (!empty($validated['parent_id'])) {
            $rootParentId = Quotation::where('id', $validated['parent_id'])->value('parent_id') ?? $validated['parent_id'];
            $maxRevision = Quotation::where('id', $rootParentId)
                ->orWhere('parent_id', $rootParentId)
                ->max('revision_number');
            $revisionNumber = max(0, (int)$maxRevision) + 1;
        }

        $quotation = $user->quotations()->create([
            'client_id' => $client->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'revision_number' => $revisionNumber,
            'quotation_number' => $validated['quotation_number'],
            'quotation_date' => $validated['quotation_date'],
            'valid_until' => $validated['valid_until'] ?? null,
            'quotation_type' => $validated['quotation_type'],
            'place_of_supply' => $validated['place_of_supply'] ?? null,
            'currency' => $validated['currency'],
            'subtotal' => $subtotal,
            'cgst' => $cgst,
            'sgst' => $sgst,
            'igst' => $igst,
            'total' => $total,
            'status' => 'draft',
            'client_notes' => $validated['client_notes'] ?? null,
            'terms_conditions' => $validated['terms_conditions'] ?? null,
            'is_active' => empty($validated['parent_id']), // Only V0 is active by default
            'custom_fields' => $this->normalizeDocumentCustomFields($validated['custom_fields'] ?? []),
        ]);

        foreach ($validated['items'] as $item) {
            $quotation->items()->create([
                'description' => $item['description'],
                'hsn_code' => $item['hsn_code'] ?? null,
                'tax_rate' => $item['tax_rate'] ?? 0,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'amount' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return redirect()->route('quotations.index')->with('success', 'Quotation created successfully.');
    }

    public function show(Quotation $quotation)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->isCompanyAdmin() && $quotation->user_id !== $user->id) {
            abort(403);
        } elseif ($user->isClientUser() && $quotation->client_id !== $user->client_id) {
            abort(403);
        } elseif (!$user->isCompanyAdmin() && !$user->isClientUser()) {
            abort(403);
        }

        $rootId = $quotation->parent_id ?? $quotation->id;
        $revisions = Quotation::where('id', $rootId)
            ->orWhere('parent_id', $rootId)
            ->orderBy('revision_number', 'asc')
            ->get();

        $quotation->load(['client', 'items', 'notes.user']);
        return view('quotations.show', compact('quotation', 'revisions'));
    }

    public function markAsActive(Quotation $quotation)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($quotation->user_id !== $user->id) abort(403);

        if ($quotation->isLocked() && !$quotation->isApproved()) {
            return redirect()->back()->with('error', 'The active revision is locked to the approved version.');
        }

        $rootId = $quotation->parent_id ?? $quotation->id;

        // Set all related versions to inactive
        Quotation::where('id', $rootId)
            ->orWhere('parent_id', $rootId)
            ->update(['is_active' => false]);

        // Set this version to active
        $quotation->update(['is_active' => true]);

        return redirect()->back()->with('success', 'Revision V' . $quotation->revision_number . ' marked as active.');
    }

    public function edit(Quotation $quotation)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($quotation->user_id !== $user->id) abort(403);
        if ($quotation->isLocked()) {
            return redirect()->route('quotations.show', $quotation)->with('error', 'This quotation series is locked and cannot be edited.');
        }

        $clients = $user->clients;
        $quotation->load('items');
        $companyCustomFields = $this->getCompanyCustomFields($user, 'show_in_quotation');
        $existingCF = collect($quotation->custom_fields ?? [])->keyBy('key');

        $quotationItems = $quotation->items->map(function($item) {
            return [
                'description' => $item->description,
                'hsn_code' => $item->hsn_code,
                'tax_rate' => (int) $item->tax_rate,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price
            ];
        });

        return view('quotations.edit', compact('quotation', 'clients', 'quotationItems', 'companyCustomFields', 'existingCF'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($quotation->user_id !== $user->id) abort(403);
        if ($quotation->isLocked()) {
            abort(403, 'Approved quotations cannot be updated.');
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'quotation_number' => 'required|string|unique:quotations,quotation_number,' . $quotation->id,
            'quotation_date' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:quotation_date',
            'quotation_type' => 'required|string|in:regular,export,interstate',
            'place_of_supply' => 'nullable|string|size:2',
            'currency' => 'required|string|size:3',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.hsn_code' => 'nullable|string',
            'items.*.tax_rate' => 'required|numeric|in:0,5,12,18,28',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'custom_fields' => 'nullable|array',
            'custom_fields.*.key' => 'required|string|max:100',
            'custom_fields.*.value' => 'nullable|string|max:255',
            'status' => 'required|in:draft,sent,approved,rejected',
            'client_notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
        ]);

        $client = Client::findOrFail($validated['client_id']);
         if ($client->user_id !== $user->id) abort(403);

        $subtotal = 0;
        $cgst = 0;
        $sgst = 0;
        $igst = 0;
        $quotationType = $validated['quotation_type'];

        foreach ($validated['items'] as $item) {
            $itemTotal = $item['quantity'] * $item['unit_price'];
            $subtotal += $itemTotal;
            
            $taxRate = $item['tax_rate'] ?? 0;
            $taxAmount = $itemTotal * ($taxRate / 100);

            if ($quotationType === 'export' || $quotationType === 'interstate') {
                 $igst += $taxAmount; 
            } else {
                 $cgst += $taxAmount / 2;
                 $sgst += $taxAmount / 2;
            }
        }

        $total = $subtotal + $cgst + $sgst + $igst;

        $quotation->update([
            'client_id' => $client->id,
            'quotation_number' => $validated['quotation_number'],
            'quotation_date' => $validated['quotation_date'],
            'valid_until' => $validated['valid_until'] ?? null,
            'quotation_type' => $validated['quotation_type'],
            'place_of_supply' => $validated['place_of_supply'] ?? null,
            'currency' => $validated['currency'],
            'subtotal' => $subtotal,
            'cgst' => $cgst,
            'sgst' => $sgst,
            'igst' => $igst,
            'total' => $total,
            'status' => $validated['status'],
            'client_notes' => $validated['client_notes'] ?? null,
            'terms_conditions' => $validated['terms_conditions'] ?? null,
            'custom_fields' => $this->normalizeDocumentCustomFields($validated['custom_fields'] ?? []),
        ]);

        $quotation->items()->delete();
        foreach ($validated['items'] as $item) {
            $quotation->items()->create([
                'description' => $item['description'],
                'hsn_code' => $item['hsn_code'] ?? null,
                'tax_rate' => $item['tax_rate'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'amount' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return redirect()->route('quotations.show', $quotation)->with('success', 'Quotation updated successfully.');
    }

    public function destroy(Quotation $quotation)
    {
        if ($quotation->user_id !== Auth::id()) abort(403);
        if ($quotation->isLocked()) {
            return redirect()->back()->with('error', 'Finalized quotation records cannot be deleted.');
        }
        $quotation->delete();
        return redirect()->route('quotations.index')->with('success', 'Quotation deleted successfully.');
    }

    public function convertToInvoice(Quotation $quotation)
    {
        if ($quotation->user_id !== Auth::id()) abort(403);
        if (!$quotation->isApproved() || $quotation->isConverted()) {
            return redirect()->back()->with('error', 'Only approved quotations can be converted to an invoice, and only once.');
        }

        // Generate Invoice Number safely
        $invoiceNumber = 'INV-' . strtoupper(Str::random(8));

        $invoice = Invoice::create([
            'user_id' => $quotation->user_id,
            'client_id' => $quotation->client_id,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'invoice_type' => $quotation->quotation_type,
            'place_of_supply' => $quotation->place_of_supply,
            'currency' => $quotation->currency,
            'subtotal' => $quotation->subtotal,
            'cgst' => $quotation->cgst,
            'sgst' => $quotation->sgst,
            'igst' => $quotation->igst,
            'total' => $quotation->total,
            'status' => 'draft',
            'notes' => 'Converted from Quotation ' . $quotation->quotation_number,
        ]);

        foreach ($quotation->items as $item) {
            $invoice->items()->create([
                'description' => $item->description,
                'hsn_code' => $item->hsn_code,
                'tax_rate' => $item->tax_rate,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'amount' => $item->amount,
            ]);
        }

        $quotation->update([
            'invoice_id' => $invoice->id,
            'status' => 'approved' // ensure it stays approved even though it's converted
        ]);

        return redirect()->route('invoices.show', $invoice)->with('success', 'Successfully converted quotation to invoice.');
    }

    public function print(Quotation $quotation)
    {
        $user = Auth::user();
        if ($user->isCompanyAdmin() && $quotation->user_id !== $user->id) abort(403);
        elseif ($user->isClientUser() && $quotation->client_id !== $user->client_id) abort(403);
        
        $quotation->load(['client', 'items', 'user.company']);
        return view('quotations.print', compact('quotation'));
    }

    public function downloadPdf(Quotation $quotation)
    {
        $user = Auth::user();
        if ($user->isCompanyAdmin() && $quotation->user_id !== $user->id) abort(403);
        elseif ($user->isClientUser() && $quotation->client_id !== $user->client_id) abort(403);

        $quotation->load(['client', 'items', 'user.company']);
        $pdf = Pdf::loadView('quotations.print', compact('quotation'));
        $safeNumber = str_replace(['/', '\\'], '-', $quotation->quotation_number);
        return $pdf->download('Quotation-' . $safeNumber . '.pdf');
    }
}
