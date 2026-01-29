<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Store a newly created payment in storage.
     */
    public function store(Request $request, Invoice $invoice)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Only Company Admins can record payments
        if (!$user->isCompanyAdmin()) {
            abort(403);
        }

        // Verify invoice belongs to the company
        if ($invoice->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . ($invoice->total - $invoice->payments()->sum('amount')),
            'payment_date' => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'transaction_reference' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $payment = $invoice->payments()->create([
            'client_id' => $invoice->client_id,
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'payment_method' => $validated['payment_method'] ?? null,
            'transaction_reference' => $validated['transaction_reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Auto-update invoice status
        // Refresh the relationship to include the new payment
        $invoice->load('payments');
        $totalPaid = $invoice->payments->sum('amount');
        
        // Force comparison of floats to be loose or use a small epsilon if strictly needed
        // but casting to float usually works for monetary values in simple equality checks
        // if they are exact.
        // However, invoice total is from DB (string/decimal) and sum is float.
        if ((float)$totalPaid >= (float)$invoice->total) {
            $invoice->update(['status' => 'paid']);
        }

        return redirect()->back()->with('success', 'Payment recorded successfully.');
    }
}
