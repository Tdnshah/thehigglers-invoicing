@include('documents.partials.bank-panel', ['company' => $invoice->user->company ?? \App\Models\Company::first()])
