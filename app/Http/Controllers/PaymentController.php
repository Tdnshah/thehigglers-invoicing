<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceRevision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Record a payment against an invoice.
     *
     * Payments are only accepted between approval and full settlement: an
     * unapproved invoice cannot take money, and a paid one is closed. Part
     * payments are allowed, and each one writes a revision carrying the total,
     * the amount received to date, and the balance still due.
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

        if (! $invoice->isApproved()) {
            return redirect()->back()->with('error', 'This invoice has to be approved before a payment can be recorded against it.');
        }

        if ($invoice->isPaid()) {
            return redirect()->back()->with('error', 'This invoice is fully paid and closed. It cannot be changed.');
        }

        $balance = $invoice->balanceDue();

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $balance,
            'payment_date' => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'transaction_reference' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ], [
            'amount.max' => 'The balance due is only ' . number_format($balance, 2) . '.',
        ]);

        DB::transaction(function () use ($invoice, $validated, $user) {
            $payment = $invoice->payments()->create([
                'client_id' => $invoice->client_id,
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'] ?? null,
                'transaction_reference' => $validated['transaction_reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Recount from the ledger rather than trusting the running figure.
            $invoice->load('payments');

            if ($invoice->balanceDue() <= 0.0) {
                $invoice->update(['status' => 'paid']);
            }

            $invoice->recordRevision(
                InvoiceRevision::EVENT_PAYMENT,
                $validated['notes'] ?? null,
                $payment,
                $user->id
            );
        });

        $invoice->refresh()->load('payments');

        $message = $invoice->isPaid()
            ? 'Payment recorded. The invoice is now fully paid and closed.'
            : 'Part payment recorded. Balance due is ' . number_format($invoice->balanceDue(), 2) . '.';

        return redirect()->back()->with('success', $message);
    }
}
