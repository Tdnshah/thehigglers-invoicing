<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Mail\InvoiceCreated;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

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
        return view('invoices.create', compact('clients'));
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

        $request->merge([
            'invoice_number' => $request->invoice_number ?? 'INV-' . strtoupper(Str::random(8)), // Fallback auto-generation
        ]);

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'invoice_number' => 'required|string|unique:invoices,invoice_number',
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
            'notes' => 'nullable|string',
        ]);

        $client = Client::findOrFail($validated['client_id']);
        
        // Ensure client belongs to authenticated user (Company Admin)
        if ($client->user_id !== Auth::id()) {
            abort(403);
        }

        $subtotal = 0;
        $totalTax = 0;
        
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

        $invoice = $user->invoices()->create([
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
            'status' => 'draft', // Default status
            'notes' => $validated['notes'] ?? null,
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

        $invoice->load(['client', 'items']);
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Display a print-friendly version of the invoice.
     */
    public function print(Invoice $invoice)
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

        $invoice->load(['client', 'items', 'user.company']);
        return view('invoices.print', compact('invoice'));
    }

    /**
     * Download the invoice as a PDF.
     */
    public function downloadPdf(Invoice $invoice)
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

        $invoice->load(['client', 'items', 'user.company']);
        
        $pdf = Pdf::loadView('invoices.print', compact('invoice'));
        return $pdf->download('Invoice-' . $invoice->invoice_number . '.pdf');
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

        $invoiceItems = $invoice->items->map(function($item) {
            return [
                'description' => $item->description,
                'hsn_code' => $item->hsn_code,
                'tax_rate' => (int) $item->tax_rate,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price
            ];
        });

        return view('invoices.edit', compact('invoice', 'clients', 'invoiceItems'));
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
            'invoice_number' => 'required|string|unique:invoices,invoice_number,' . $invoice->id,
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
            'status' => 'required|in:draft,sent,paid,overdue',
            'notes' => 'nullable|string',
        ]);

        $client = Client::findOrFail($validated['client_id']);
        
         if ($client->user_id !== $user->id) {
            abort(403);
        }

        $subtotal = 0;
        $totalTax = 0;
        
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
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
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
