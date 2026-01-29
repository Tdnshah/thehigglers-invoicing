<x-mail::message>
# Invoice Created

Dear {{ $clientName }},

A new invoice **{{ $invoice->invoice_number }}** has been created for your account.

**Invoice Date:** {{ $invoice->invoice_date->format('d M, Y') }}<br>
**Due Date:** {{ $dueDate }}<br>
**Amount Due:** {{ $invoice->currency }} {{ number_format($amount, 2) }}

Please find the invoice attached to this email.

<x-mail::button :url="$invoiceUrl">
View Invoice
</x-mail::button>

Thanks,<br>
{{ $invoice->user->company->name ?? config('app.name') }}
</x-mail::message>
