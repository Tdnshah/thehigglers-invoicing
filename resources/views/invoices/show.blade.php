<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Invoice Details') }}
            </h2>
            <div>
                <a href="{{ route('invoices.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Back to List</a>
                <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-2">
                    Print View
                </a>
                <a href="{{ route('invoices.download', $invoice) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Download PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" id="invoice-print-area">
                <div class="p-8 text-gray-900">
                    <!-- Invoice Container -->
                    <div class="max-w-4xl mx-auto p-6 border border-gray-100 shadow-sm rounded-lg bg-white">
                        
                        <!-- Header: Company & Invoice Info -->
                    <div class="flex justify-between items-start mb-8 border-b pb-8">
                        <div>
                            @php
                                $company = $invoice->user->company ?? \App\Models\Company::first();
                            @endphp
                            @if($company->logo_path)
                                <img src="{{ asset('storage/' . $company->logo_path) }}" alt="Company Logo" class="h-20 mb-4">
                            @endif
                            <h1 class="text-3xl font-bold text-gray-800 mb-2">INVOICE</h1>
                            <div class="text-gray-600">
                                <p class="font-bold text-lg">{{ $company->name }}</p>
                                <p>{{ $company->address }}</p>
                                <p>Phone: {{ $company->phone }}</p>
                                <p>Email: {{ $company->email }}</p>
                                @if($company->gst_number)
                                    <p class="mt-1"><strong>GSTIN:</strong> {{ $company->gst_number }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="mb-4">
                                <span class="block text-gray-500 text-sm uppercase font-bold">Invoice #</span>
                                <span class="font-bold text-xl">{{ $invoice->invoice_number }}</span>
                            </div>
                            <div class="mb-4">
                                <span class="block text-gray-500 text-sm uppercase font-bold">Date</span>
                                <span>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('M d, Y') }}</span>
                            </div>
                            @if($invoice->due_date)
                            <div class="mb-4">
                                <span class="block text-gray-500 text-sm uppercase font-bold">Due Date</span>
                                <span>{{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}</span>
                            </div>
                            @endif
                            <div>
                                <span class="block text-gray-500 text-sm uppercase font-bold">Status</span>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : 
                                      ($invoice->status === 'overdue' ? 'bg-red-100 text-red-800' : 
                                      ($invoice->status === 'sent' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800')) }}">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Client Details -->
                    <div class="mb-8">
                        <h3 class="text-gray-500 text-sm uppercase font-bold mb-2">Bill To:</h3>
                        <div class="text-gray-800">
                            <p class="font-bold text-lg">{{ $invoice->client->name }}</p>
                            <p class="whitespace-pre-line">{{ $invoice->client->address }}</p>
                            @if($invoice->client->email)
                                <p>Email: {{ $invoice->client->email }}</p>
                            @endif
                            @if($invoice->client->phone)
                                <p>Phone: {{ $invoice->client->phone }}</p>
                            @endif
                            @if($invoice->client->gst_number)
                                <p class="mt-1"><strong>GSTIN:</strong> {{ $invoice->client->gst_number }}</p>
                            @endif
                            <p class="mt-1"><strong>Place of Supply:</strong> {{ $invoice->place_of_supply ?? 'N/A' }}</p>
                            @if($invoice->invoice_type === 'export' && $invoice->lut_number)
                                <p class="mt-1"><strong>LUT Number:</strong> {{ $invoice->lut_number }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="overflow-x-auto mb-8">
                        <table class="min-w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Description</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border-b">HSN/SAC</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Tax Rate</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Quantity</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Price</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($invoice->items as $item)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $item->description }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ $item->hsn_code ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ $item->tax_rate }}%</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ $item->quantity }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right">
                                             @php
                                                $taxAmount = ($item->quantity * $item->unit_price) * ($item->tax_rate / 100);
                                                $lineTotal = ($item->quantity * $item->unit_price) + $taxAmount;
                                             @endphp
                                            {{ $invoice->currency }} {{ number_format($lineTotal, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals -->
                    <div class="flex justify-end mb-8">
                        <div class="w-1/2 sm:w-1/3">
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="font-semibold text-gray-600">Subtotal</span>
                                <span class="font-semibold text-gray-900">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</span>
                            </div>
                            
                            @if($invoice->igst > 0)
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-gray-600">IGST</span>
                                    <span class="text-gray-900">{{ $invoice->currency }} {{ number_format($invoice->igst, 2) }}</span>
                                </div>
                            @elseif($invoice->cgst > 0 || $invoice->sgst > 0)
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-gray-600">CGST</span>
                                    <span class="text-gray-900">{{ $invoice->currency }} {{ number_format($invoice->cgst, 2) }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-gray-600">SGST</span>
                                    <span class="text-gray-900">{{ $invoice->currency }} {{ number_format($invoice->sgst, 2) }}</span>
                                </div>
                            @endif

                            <div class="flex justify-between py-2 border-t-2 border-gray-200 mt-2">
                                <span class="font-bold text-lg text-gray-800">Total</span>
                                <span class="font-bold text-lg text-gray-800">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</span>
                            </div>
                        </div>
                    </div>

                        </div>
                    </div>

                    <!-- Payments Section (Only visible to admins or if payments exist) -->
                    @if(Auth::user()->isCompanyAdmin() || $invoice->payments->count() > 0)
                    <div class="mb-8 border-t pt-8">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Payments History</h3>
                        
                        @if($invoice->payments->count() > 0)
                            <div class="overflow-x-auto mb-4">
                                <table class="min-w-full border-collapse">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach($invoice->payments as $payment)
                                            <tr>
                                                <td class="px-4 py-2 text-sm text-gray-900">{{ $payment->payment_date->format('M d, Y') }}</td>
                                                <td class="px-4 py-2 text-sm text-gray-900">{{ $payment->payment_method ?? '-' }}</td>
                                                <td class="px-4 py-2 text-sm text-gray-900">{{ $payment->transaction_reference }}</td>
                                                <td class="px-4 py-2 text-sm text-gray-900 text-right">{{ $invoice->currency }} {{ number_format($payment->amount, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-gray-500 text-sm mb-4">No payments recorded yet.</p>
                        @endif

                        <!-- Add Payment Form (Admin Only) -->
                        @if(Auth::user()->isCompanyAdmin() && $invoice->status !== 'paid')
                            <div class="bg-gray-50 p-4 rounded-lg mt-4 no-print">
                                <h4 class="font-bold text-gray-700 mb-2">Record Payment</h4>
                                <form action="{{ route('payments.store', $invoice) }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                                    @csrf
                                    <div>
                                        <x-input-label for="amount" :value="__('Amount')" />
                                        <x-text-input id="amount" class="block mt-1 w-full" type="number" step="0.01" name="amount" :value="old('amount', $invoice->total - $invoice->payments()->sum('amount'))" required />
                                    </div>
                                    <div>
                                        <x-input-label for="payment_date" :value="__('Date')" />
                                        <x-text-input id="payment_date" class="block mt-1 w-full" type="date" name="payment_date" :value="date('Y-m-d')" required />
                                    </div>
                                    <div>
                                        <x-input-label for="payment_method" :value="__('Method')" />
                                        <select id="payment_method" name="payment_method" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            <option value="Bank Transfer">Bank Transfer</option>
                                            <option value="Cash">Cash</option>
                                            <option value="Cheque">Cheque</option>
                                            <option value="UPI">UPI</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    <div>
                                        <x-input-label for="transaction_reference" :value="__('Reference')" />
                                        <x-text-input id="transaction_reference" class="block mt-1 w-full" type="text" name="transaction_reference" required />
                                    </div>
                                    <div>
                                        <x-primary-button>
                                            {{ __('Record Payment') }}
                                        </x-primary-button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                    @endif

                        <!-- Footer / Bank Details / Notes -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 border-t pt-8">
                            @if($invoice->notes)
                                <div>
                                    <h4 class="font-bold text-gray-700 mb-2">Notes</h4>
                                    <p class="text-sm text-gray-600 whitespace-pre-line">{{ $invoice->notes }}</p>
                                </div>
                            @endif

                            <div class="text-sm text-gray-600">
                                <h4 class="font-bold text-gray-700 mb-2">Bank Details</h4>
                                @if($company->bank_name)
                                    <p>Bank Name: {{ $company->bank_name }}</p>
                                @endif
                                @if($company->bank_account_number)
                                    <p>Account Number: {{ $company->bank_account_number }}</p>
                                @endif
                                @if($company->bank_ifsc)
                                    <p>IFSC Code: {{ $company->bank_ifsc }}</p>
                                @endif
                            </div>
                        </div>
                    
                    </div><!-- End Invoice Container -->
                </div>
            </div>
        </div>
    </div>
    
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #invoice-print-area, #invoice-print-area * {
                visibility: visible;
            }
            #invoice-print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                box-shadow: none;
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</x-app-layout>
