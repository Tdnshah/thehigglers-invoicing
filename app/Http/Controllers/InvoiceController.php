<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceRevision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Mail\InvoiceCreated;
use App\Services\DocumentNumberGenerator;
use App\Services\DocumentRenderer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->isCompanyAdmin()) {
            // Company Admin sees all invoices created by the company
            $invoices = Invoice::where('user_id', $user->id)
                ->with('client')
                ->latest()
                ->paginate(10);
        } elseif ($user->isClientUser()) {
            // Client User sees only invoices assigned to their client_id
            $invoices = Invoice::where('client_id', $user->client_id)
                ->with('client')
                ->latest()
                ->paginate(10);
        } else {
            // Fallback (should not happen in current logic)
            $invoices = collect([]); 
        }

        return view('invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Only Company Admins can create invoices
        if (!$user->isCompanyAdmin()) {
            abort(403, 'Only administrators can create invoices.');
        }

        $clients = $user->clients;
        $companyCustomFields = $this->getCompanyCustomFields($user, 'show_in_invoice');
        $lutOptions = $user->company?->lutOptions() ?? collect();
        $company = $user->company;
        return view('invoices.create', compact('clients', 'companyCustomFields', 'lutOptions', 'company'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Only Company Admins can create invoices
        if (!$user->isCompanyAdmin()) {
            abort(403, 'Only administrators can create invoices.');
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'invoice_number' => ['nullable', 'string', 'max:' . DocumentNumberGenerator::MAX_LENGTH, 'regex:' . DocumentNumberGenerator::ALLOWED_PATTERN, 'unique:invoices,invoice_number'],
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'invoice_type' => 'required|string|in:regular,export,interstate',
            'place_of_supply' => 'nullable|string|size:2', // e.g., 27 for Maharashtra
            'lut_number' => 'nullable|required_if:invoice_type,export|string',
            'currency' => 'required|string|size:3',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.hsn_code' => 'nullable|string',
            'items.*.tax_rate' => 'nullable|numeric|in:0,5,12,18,28', // Made nullable for tests/defaults
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'custom_fields' => 'nullable|array',
            'custom_fields.*.key' => 'required|string|max:100',
            'custom_fields.*.value' => 'nullable|string|max:255',
        ] + $this->documentPresentationRules());

        $client = Client::findOrFail($validated['client_id']);
        
        // Ensure client belongs to authenticated user (Company Admin)
        if ($client->user_id !== Auth::id()) {
            abort(403);
        }

        $subtotal = 0;
        
        // GST Calculation Logic
        $invoiceType = $validated['invoice_type'];
        
        // If regular, auto-detect interstate vs intrastate if place_of_supply not manually set?
        // Or strictly follow invoice_type. 
        // User Requirement: "i need to tell if it is a interstate, intrastate or export service"
        // So we follow invoice_type strictly.

        $cgst = 0;
        $sgst = 0;
        $igst = 0;

        foreach ($validated['items'] as $item) {
            $itemTotal = $item['quantity'] * $item['unit_price'];
            $subtotal += $itemTotal;
            
            // Calculate tax for this item
            $taxRate = $item['tax_rate'] ?? 0;
            $taxAmount = $itemTotal * ($taxRate / 100);

            if ($invoiceType === 'export') {
                 // Export: Zero Rated (Usually). But if tax is applied, it's typically IGST or 0.
                 // Requirement: "export service ... supply under zero rated tax"
                 // So we force tax to 0 effectively, or user should select 0% tax.
                 // However, if they select 18%, it might be "Export with Payment of Tax".
                 // BUT user said: "items will be supply inder zero rated tax".
                 // So we ignore tax calculation for totals? Or assume user selects 0%?
                 // Let's assume standard behavior: Export = IGST (if with payment) or 0 (if LUT).
                 // User mentioned LUT, so likely 0 tax.
                 // We'll calculate IGST if rate > 0, but usually with LUT it is 0.
                 // Let's apply to IGST bucket if any.
                 $igst += $taxAmount; 

            } elseif ($invoiceType === 'interstate') {
                 $igst += $taxAmount;
            } else { // regular / intrastate
                 $cgst += $taxAmount / 2;
                 $sgst += $taxAmount / 2;
            }
        }

        $total = $subtotal + $cgst + $sgst + $igst;

        // A blank number means "use the company's numbering format".
        $invoiceNumber = filled($validated['invoice_number'] ?? null)
            ? $validated['invoice_number']
            : app(DocumentNumberGenerator::class)->next(
                DocumentNumberGenerator::INVOICE,
                $user->company,
                $client,
                \Illuminate\Support\Carbon::parse($validated['invoice_date'])
            );

        $invoice = $user->invoices()->create([
            'client_id' => $client->id,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'] ?? null,
            'invoice_type' => $validated['invoice_type'],
            'place_of_supply' => $validated['place_of_supply'] ?? null,
            'lut_number' => $validated['lut_number'] ?? null,
            'currency' => $validated['currency'],
            'subtotal' => $subtotal,
            'cgst' => $cgst,
            'sgst' => $sgst,
            'igst' => $igst,
            'total' => $total,
            'status' => 'draft', // Default status
            'terms_mode' => $validated['terms_mode'] ?? 'global',
            'terms_conditions' => $validated['terms_conditions'] ?? null,
            'bank_mode' => $validated['bank_mode'] ?? 'account',
            'bank_account_id' => $validated['bank_account_id'] ?? null,
            'bank_details' => $this->normalizeBankRows($validated['bank_details'] ?? []),
        ]);

        foreach ($validated['items'] as $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'hsn_code' => $item['hsn_code'] ?? null,
                'tax_rate' => $item['tax_rate'] ?? 0,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'amount' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        $invoice->update([
            'custom_fields' => $this->normalizeDocumentCustomFields($validated['custom_fields'] ?? []),
        ]);

        // Send email to client if they have an email address
        if ($client->email) {
            Mail::to($client->email)->send(new InvoiceCreated($invoice));
        }

        return redirect()->route('invoices.index')->with('success', 'Invoice created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Access Control
        if ($user->isCompanyAdmin()) {
            // Company Admin: Must own the invoice
            if ($invoice->user_id !== $user->id) {
                abort(403);
            }
        } elseif ($user->isClientUser()) {
            // Client User: Invoice must belong to their client_id
            if ($invoice->client_id !== $user->client_id) {
                abort(403);
            }
        } else {
            abort(403);
        }

        $relations = ['client', 'items', 'quotation', 'payments'];

        // Internal history is never loaded for a client user, so a view level
        // mistake cannot leak it.
        if ($user->isCompanyAdmin()) {
            $relations = array_merge($relations, ['privateNotes.user', 'revisions.user', 'approvedBy']);
        }

        $invoice->load($relations);

        $allowed = array_values(array_filter([
            'document',
            ($user->isCompanyAdmin() || $invoice->payments->count()) ? 'payments' : null,
            $user->isCompanyAdmin() ? 'notes' : null,
            $user->isCompanyAdmin() ? 'revisions' : null,
            'bank',
        ]));

        $tab = $this->resolveTab(request('tab'), $allowed);

        return view('invoices.show', compact('invoice', 'tab'));
    }

    /**
     * Display a print-friendly version of the invoice.
     */
    public function print(Invoice $invoice, DocumentRenderer $renderer)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Access Control
        if ($user->isCompanyAdmin()) {
            if ($invoice->user_id !== $user->id) {
                abort(403);
            }
        } elseif ($user->isClientUser()) {
            if ($invoice->client_id !== $user->client_id) {
                abort(403);
            }
        } else {
            abort(403);
        }

        return $renderer->view($renderer->forInvoice($invoice));
    }

    /**
     * Download the invoice as a PDF.
     */
    public function downloadPdf(Invoice $invoice, DocumentRenderer $renderer)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Access Control
        if ($user->isCompanyAdmin()) {
            if ($invoice->user_id !== $user->id) {
                abort(403);
            }
        } elseif ($user->isClientUser()) {
            if ($invoice->client_id !== $user->client_id) {
                abort(403);
            }
        } else {
            abort(403);
        }

        $document = $renderer->forInvoice($invoice);

        return $renderer->pdf($document)->download($renderer->filename($document));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($invoice->user_id !== $user->id) {
            abort(403);
        }

        if ($invoice->status === 'paid') {
            return redirect()->route('invoices.show', $invoice)->with('error', 'Paid invoices cannot be edited.');
        }

        $clients = $user->clients;
        $invoice->load('items');
        $companyCustomFields = $this->getCompanyCustomFields($user, 'show_in_invoice');
        $existingCF = collect($invoice->custom_fields ?? [])->keyBy('key');

        $invoiceItems = $invoice->items->map(function($item) {
            return [
                'description' => $item->description,
                'hsn_code' => $item->hsn_code,
                'tax_rate' => (int) $item->tax_rate,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price
            ];
        });

        $lutOptions = $user->company?->lutOptions() ?? collect();

        $company = $user->company;

        return view('invoices.edit', compact('invoice', 'clients', 'invoiceItems', 'companyCustomFields', 'existingCF', 'lutOptions', 'company'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($invoice->user_id !== $user->id) {
            abort(403);
        }

        if ($invoice->status === 'paid') {
            abort(403, 'Paid invoices cannot be updated.');
        }

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'invoice_number' => ['required', 'string', 'max:' . DocumentNumberGenerator::MAX_LENGTH, 'regex:' . DocumentNumberGenerator::ALLOWED_PATTERN, 'unique:invoices,invoice_number,' . $invoice->id],
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'invoice_type' => 'required|string|in:regular,export,interstate',
            'place_of_supply' => 'nullable|string|size:2', // e.g., 27 for Maharashtra
            'lut_number' => 'nullable|required_if:invoice_type,export|string',
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
        ] + $this->documentPresentationRules());

        $client = Client::findOrFail($validated['client_id']);
        
         if ($client->user_id !== $user->id) {
            abort(403);
        }

        $subtotal = 0;
        
        // GST Calculation Logic
        $invoiceType = $validated['invoice_type'];

        $cgst = 0;
        $sgst = 0;
        $igst = 0;

        foreach ($validated['items'] as $item) {
            $itemTotal = $item['quantity'] * $item['unit_price'];
            $subtotal += $itemTotal;
            
            // Calculate tax for this item
            $taxRate = $item['tax_rate'] ?? 0;
            $taxAmount = $itemTotal * ($taxRate / 100);

            if ($invoiceType === 'export') {
                 $igst += $taxAmount; 
            } elseif ($invoiceType === 'interstate') {
                 $igst += $taxAmount;
            } else { // regular / intrastate
                 $cgst += $taxAmount / 2;
                 $sgst += $taxAmount / 2;
            }
        }

        $total = $subtotal + $cgst + $sgst + $igst;

        $invoice->update([
            'terms_mode' => $validated['terms_mode'] ?? 'global',
            'terms_conditions' => $validated['terms_conditions'] ?? null,
            'bank_mode' => $validated['bank_mode'] ?? 'account',
            'bank_account_id' => $validated['bank_account_id'] ?? null,
            'bank_details' => $this->normalizeBankRows($validated['bank_details'] ?? []),
            'client_id' => $client->id,
            'invoice_number' => $validated['invoice_number'],
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'] ?? null,
            'invoice_type' => $validated['invoice_type'],
            'place_of_supply' => $validated['place_of_supply'] ?? null,
            'lut_number' => $validated['lut_number'] ?? null,
            'currency' => $validated['currency'],
            'subtotal' => $subtotal,
            'cgst' => $cgst,
            'sgst' => $sgst,
            'igst' => $igst,
            'total' => $total,
            'custom_fields' => $this->normalizeDocumentCustomFields($validated['custom_fields'] ?? []),
        ]);

        // Sync items: Delete old and create new (simpler than updating individually)
        $invoice->items()->delete();
        foreach ($validated['items'] as $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'hsn_code' => $item['hsn_code'] ?? null,
                'tax_rate' => $item['tax_rate'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'amount' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return redirect()->route('invoices.index')->with('success', 'Invoice updated successfully.');
    }

    /**
     * Approve an invoice. Approval is the gate that unlocks payments, so it
     * carries a mandatory note explaining the decision; the note lands in the
     * internal thread and on the approval revision.
     */
    public function approve(Request $request, Invoice $invoice)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user->isCompanyAdmin() || $invoice->user_id !== $user->id) {
            abort(403);
        }

        if ($invoice->isApproved()) {
            return redirect()->route('invoices.show', $invoice)->with('error', 'This invoice is already approved.');
        }

        if ($invoice->isPaid()) {
            return redirect()->route('invoices.show', $invoice)->with('error', 'This invoice is settled and closed. It cannot be changed.');
        }

        $validated = $request->validate([
            'note' => 'required|string|min:3|max:1000',
        ], [
            'note.required' => 'An approval note is required. Say why this invoice is being approved.',
        ]);

        DB::transaction(function () use ($invoice, $user, $validated) {
            $invoice->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $user->id,
            ]);

            $invoice->privateNotes()->create([
                'user_id' => $user->id,
                'note' => 'Approved: ' . $validated['note'],
            ]);

            $invoice->load('payments');
            $invoice->recordRevision(InvoiceRevision::EVENT_APPROVED, $validated['note'], null, $user->id);
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice approved. Payments can now be recorded against it.');
    }

    /**
     * Statuses a viewer may set by hand.
     *
     * Approval runs through approve() because it needs a note, and paid is
     * derived from the payments ledger, so neither is offered here. A settled
     * invoice is closed to all of them.
     *
     * @return array<string, string>
     */
    public static function selectableStatuses(Invoice $invoice): array
    {
        if ($invoice->isPaid()) {
            return [];
        }

        return $invoice->isApproved()
            ? ['approved' => 'Approved', 'sent' => 'Sent', 'overdue' => 'Overdue']
            : ['draft' => 'Draft', 'sent' => 'Sent', 'overdue' => 'Overdue'];
    }

    /**
     * Change the status without opening the edit form.
     */
    public function updateStatus(Request $request, Invoice $invoice)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user->isCompanyAdmin() || $invoice->user_id !== $user->id) {
            abort(403);
        }

        $allowed = array_keys(self::selectableStatuses($invoice));

        if ($allowed === []) {
            return redirect()->back()->with('error', 'This invoice is settled and closed. Its status cannot be changed.');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in($allowed)],
        ], [
            'status.in' => 'That status cannot be set here. Approval needs a note, and paid follows the payments ledger.',
        ]);

        $invoice->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Invoice marked as ' . $validated['status'] . '.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        if ($invoice->user_id !== Auth::id()) {
            abort(403);
        }
        
        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', 'Invoice deleted successfully.');
    }
}
