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

    <div x-data="{ activeView: 'document' }" class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col md:flex-row gap-8 items-start">
        
        <!-- Left Vertical Navigation Menu -->
        <div class="w-full md:w-1/4 flex flex-col space-y-2 no-print sticky top-6">
            <button @click="activeView = 'document'" :class="{'bg-indigo-600 text-white shadow-md': activeView === 'document', 'text-gray-600 hover:bg-white hover:shadow-sm border border-transparent hover:border-gray-200': activeView !== 'document'}" class="px-4 py-3 text-left rounded-lg font-bold text-sm transition-all flex items-center justify-between">
                <span>Invoice Document</span>
                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </button>

            @if(Auth::user()->isCompanyAdmin() || $invoice->payments->count() > 0)
            <button @click="activeView = 'payments'" :class="{'bg-indigo-600 text-white shadow-md': activeView === 'payments', 'text-gray-600 hover:bg-white hover:shadow-sm border border-transparent hover:border-gray-200': activeView !== 'payments'}" class="px-4 py-3 text-left rounded-lg font-bold text-sm transition-all flex items-center justify-between">
                <span>Payments</span>
                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </button>
            @endif

            <button @click="activeView = 'bank'" :class="{'bg-indigo-600 text-white shadow-md': activeView === 'bank', 'text-gray-600 hover:bg-white hover:shadow-sm border border-transparent hover:border-gray-200': activeView !== 'bank'}" class="px-4 py-3 text-left rounded-lg font-bold text-sm transition-all flex items-center justify-between">
                <span>Bank Details</span>
                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
            </button>
        </div>

        <!-- Right Main Dynamic Content Area -->
        <div class="w-full md:w-3/4">

            <!-- Document View -->
            <div x-show="activeView === 'document'" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8" id="invoice-print-area">
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

                        <!-- Footer / Notes -->
                        <div class="border-t pt-8">
                            <div class="flex flex-col space-y-6">
                                @if($invoice->notes)
                                    <div>
                                        <h4 class="font-bold text-gray-700 mb-2">Notes</h4>
                                        <p class="text-sm text-gray-600 whitespace-pre-line">{{ $invoice->notes }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div><!-- End Invoice Container -->
                </div>
            </div>

            <!-- Payments View -->
            @if(Auth::user()->isCompanyAdmin() || $invoice->payments->count() > 0)
            <div x-show="activeView === 'payments'" style="display: none;" class="bg-white shadow-md rounded-lg overflow-hidden no-print mb-8">
                <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-lg font-bold text-gray-900">Payments Ledger</h3>
                </div>
                <div class="p-6">
                    @if($invoice->payments->count() > 0)
                        <div class="overflow-hidden rounded-xl border border-gray-200 mb-8 shadow-sm">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Method</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Reference</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($invoice->payments as $payment)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->payment_date->format('M d, Y') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600">{{ $invoice->currency }} {{ number_format($payment->amount, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $payment->payment_method ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">{{ $payment->transaction_reference }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-10 bg-gray-50 rounded-lg mb-8 border border-dashed border-gray-200">
                            <p class="text-gray-500 italic">No payments recorded yet.</p>
                        </div>
                    @endif

                    @if(Auth::user()->isCompanyAdmin() && $invoice->status !== 'paid')
                    <div class="bg-indigo-50/50 p-6 rounded-xl border border-indigo-100">
                        <h4 class="font-bold text-indigo-900 mb-4 text-base">Record New Payment</h4>
                        <form action="{{ route('payments.store', $invoice) }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <x-input-label for="amount" :value="__('Amount Received')" class="text-sm font-bold" />
                                    <div class="relative mt-1">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm font-medium">{{ $invoice->currency }}</span>
                                        </div>
                                        <x-text-input id="amount" class="block w-full pl-10 sm:text-sm" type="number" step="0.01" name="amount" :value="old('amount', $invoice->total - $invoice->payments()->sum('amount'))" required />
                                    </div>
                                </div>
                                <div>
                                    <x-input-label for="payment_date" :value="__('Payment Date')" class="text-sm font-bold" />
                                    <x-text-input id="payment_date" class="block w-full mt-1 sm:text-sm" type="date" name="payment_date" :value="date('Y-m-d')" required />
                                </div>
                                <div>
                                    <x-input-label for="payment_method" :value="__('Payment Method')" class="text-sm font-bold" />
                                    <select id="payment_method" name="payment_method" class="block w-full mt-1 sm:text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="Bank Transfer">Bank Transfer</option>
                                        <option value="Cash">Cash</option>
                                        <option value="Cheque">Cheque</option>
                                        <option value="UPI">UPI</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="transaction_reference" :value="__('Transaction / Reference No.')" class="text-sm font-bold" />
                                    <x-text-input id="transaction_reference" class="block w-full mt-1 sm:text-sm font-mono" type="text" name="transaction_reference" required />
                                </div>
                            </div>
                            <div class="mt-6 text-right">
                                <button class="inline-flex items-center px-6 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 shadow-sm transition">
                                    Save Payment Record
                                </button>
                            </div>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
            @endif
            
            <!-- Bank Details View -->
            <div x-show="activeView === 'bank'" style="display: none;" class="bg-white shadow-md rounded-lg overflow-hidden no-print mb-8">
                <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-900">Company Bank Details</h3>
                </div>
                <div class="p-6">
                    @php
                        $company = $invoice->user->company ?? \App\Models\Company::first();
                    @endphp
                    <div class="grid grid-cols-1 gap-6">
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100 p-8 rounded-xl border border-gray-200 shadow-inner relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-6 opacity-[0.03]">
                                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.31-8.86c-1.77-.45-2.34-.94-2.34-1.67 0-.84.79-1.43 2.1-1.43 1.38 0 1.9.66 1.94 1.64h1.71c-.05-1.34-.87-2.57-2.49-2.97V5H10.9v1.69c-1.51.32-2.72 1.3-2.72 2.81 0 1.79 1.49 2.69 3.66 3.21 1.95.46 2.34 1.15 2.34 1.87 0 .53-.39 1.64-2.25 1.64-1.74 0-2.33-.97-2.38-1.72H7.76c.07 1.55 1.15 2.68 2.94 3.09v1.73h2.2v-1.7c1.78-.34 2.84-1.54 2.84-2.92 0-1.84-1.37-2.78-3.43-3.26z"/></svg>
                            </div>
                            @if($company->bank_name)
                                <div class="mb-5 relative z-10">
                                    <span class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Bank Name</span> 
                                    <span class="text-xl font-bold text-gray-900">{{ $company->bank_name }}</span>
                                </div>
                            @endif
                            @if($company->bank_account_number)
                                <div class="mb-5 relative z-10">
                                    <span class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Account Number</span> 
                                    <span class="text-2xl font-medium text-gray-800 font-mono tracking-widest">{{ $company->bank_account_number }}</span>
                                </div>
                            @endif
                            @if($company->bank_ifsc)
                                <div class="relative z-10">
                                    <span class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">IFSC Code</span> 
                                    <span class="text-xl font-medium text-gray-800 font-mono tracking-wider">{{ $company->bank_ifsc }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
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
