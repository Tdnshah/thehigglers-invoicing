@include('documents.partials.bank-panel', ['company' => $quotation->user->company ?? \App\Models\Company::first()])
