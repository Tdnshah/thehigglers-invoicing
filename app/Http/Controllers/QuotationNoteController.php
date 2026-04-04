<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuotationNoteController extends Controller
{
    public function store(Request $request, Quotation $quotation)
    {
        if ($quotation->user_id !== Auth::id()) abort(403);

        $validated = $request->validate([
            'note' => 'required|string|max:1000'
        ]);

        $quotation->notes()->create([
            'user_id' => Auth::id(),
            'note' => $validated['note']
        ]);

        return redirect()->route('quotations.show', $quotation)->with('success', 'Note added successfully.');
    }
}
