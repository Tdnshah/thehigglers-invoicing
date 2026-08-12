<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceNoteController extends Controller
{
    public function store(Request $request, Invoice $invoice)
    {
        if ($invoice->user_id !== Auth::id()) abort(403);

        $validated = $request->validate([
            'note' => 'required|string|max:1000'
        ]);

        $invoice->privateNotes()->create([
            'user_id' => Auth::id(),
            'note' => $validated['note']
        ]);

        return redirect()->route('invoices.show', ['invoice' => $invoice, 'tab' => 'notes'])->with('success', 'Note added.');
    }

    public function destroy(InvoiceNote $note)
    {
        if ($note->invoice->user_id !== Auth::id()) abort(403);

        $invoice = $note->invoice;
        $note->delete();

        return redirect()->route('invoices.show', ['invoice' => $invoice, 'tab' => 'notes'])->with('success', 'Note deleted.');
    }
}
