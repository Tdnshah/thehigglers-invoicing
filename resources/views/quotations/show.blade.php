<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Quotation Details') }}
            </h2>
            <div>
                <a href="{{ route('quotations.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Back to List</a>
                
                @php $isLocked = $revisions->contains(fn($r) => $r->status === 'approved'); @endphp

                @if(Auth::user()->isCompanyAdmin() && !$isLocked)
                    <a href="{{ route('quotations.edit', $quotation) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 mr-2">
                        Edit Quotation
                    </a>
                @endif

                @if($isLocked)
                    <span class="inline-flex items-center px-4 py-2 bg-green-50 border border-green-200 rounded-md font-bold text-xs text-green-700 uppercase tracking-widest mr-2">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        Finalized
                    </span>
                @endif

                <a href="{{ route('quotations.print', $quotation) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-2">
                    Print View
                </a>
                <a href="{{ route('quotations.download', $quotation) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Download PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div x-data="{ activeView: 'document' }" class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col md:flex-row gap-8 items-start">
        
        <!-- Left Vertical Navigation Menu -->
        <div class="w-full md:w-1/4 flex flex-col space-y-2 no-print sticky top-6">
            <button @click="activeView = 'document'" :class="{'bg-indigo-600 text-white shadow-md': activeView === 'document', 'text-gray-600 hover:bg-white hover:shadow-sm border border-transparent hover:border-gray-200': activeView !== 'document'}" class="px-4 py-3 text-left rounded-lg font-bold text-sm transition-all flex items-center justify-between">
                <span>Quotation Document</span>
                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </button>

            @if(Auth::user()->isCompanyAdmin())
            <button @click="activeView = 'actions'" :class="{'bg-indigo-600 text-white shadow-md': activeView === 'actions', 'text-gray-600 hover:bg-white hover:shadow-sm border border-transparent hover:border-gray-200': activeView !== 'actions'}" class="px-4 py-3 text-left rounded-lg font-bold text-sm transition-all flex items-center justify-between">
                <span>Revisions & Actions</span>
                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            </button>
            <button @click="activeView = 'notes'" :class="{'bg-indigo-600 text-white shadow-md': activeView === 'notes', 'text-gray-600 hover:bg-white hover:shadow-sm border border-transparent hover:border-gray-200': activeView !== 'notes'}" class="px-4 py-3 text-left rounded-lg font-bold text-sm transition-all flex items-center justify-between">
                <span>Internal Notes</span>
                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
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
            <div x-show="activeView === 'document'" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8" id="quotation-print-area">
                <div class="p-8 text-gray-900">
                    <!-- Quotation Container -->
                    <div class="max-w-4xl mx-auto p-6 border border-gray-100 shadow-sm rounded-lg bg-white">
                        
                        <!-- Header: Company & Quotation Info -->
                    <div class="flex justify-between items-start mb-8 border-b pb-8">
                        <div>
                            @php
                                $company = $quotation->user->company ?? \App\Models\Company::first();
                            @endphp
                            @if($company->logo_path)
                                <img src="{{ asset('storage/' . $company->logo_path) }}" alt="Company Logo" class="h-20 mb-4">
                            @endif
                            <h1 class="text-3xl font-bold text-gray-800 mb-2">QUOTATION</h1>
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
                                <span class="block text-gray-500 text-sm uppercase font-bold">Quotation #</span>
                                <span class="font-bold text-xl">{{ $quotation->quotation_number }}</span>
                            </div>
                            <div class="mb-4">
                                <span class="block text-gray-500 text-sm uppercase font-bold">Date</span>
                                <span>{{ \Carbon\Carbon::parse($quotation->quotation_date)->format('M d, Y') }}</span>
                            </div>
                            @if($quotation->valid_until)
                            <div class="mb-4">
                                <span class="block text-gray-500 text-sm uppercase font-bold">Valid Until</span>
                                <span>{{ \Carbon\Carbon::parse($quotation->valid_until)->format('M d, Y') }}</span>
                            </div>
                            @endif
                            <div>
                                <span class="block text-gray-500 text-sm uppercase font-bold">Status</span>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $quotation->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                      ($quotation->status === 'rejected' ? 'bg-red-100 text-red-800' : 
                                      ($quotation->status === 'sent' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800')) }}">
                                    {{ ucfirst($quotation->status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Client Details -->
                    <div class="mb-8">
                        <h3 class="text-gray-500 text-sm uppercase font-bold mb-2">Bill To:</h3>
                        <div class="text-gray-800">
                            <p class="font-bold text-lg">{{ $quotation->client->name }}</p>
                            <p class="whitespace-pre-line">{{ $quotation->client->address }}</p>
                            @if($quotation->client->email)
                                <p>Email: {{ $quotation->client->email }}</p>
                            @endif
                            @if($quotation->client->phone)
                                <p>Phone: {{ $quotation->client->phone }}</p>
                            @endif
                            @if($quotation->client->gst_number)
                                <p class="mt-1"><strong>GSTIN:</strong> {{ $quotation->client->gst_number }}</p>
                            @endif
                            <p class="mt-1"><strong>Place of Supply:</strong> {{ $quotation->place_of_supply ?? 'N/A' }}</p>
                            @if($quotation->quotation_type === 'export' && $quotation->lut_number)
                                <p class="mt-1"><strong>LUT Number:</strong> {{ $quotation->lut_number }}</p>
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
                                @foreach($quotation->items as $item)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $item->description }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ $item->hsn_code ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ $item->tax_rate }}%</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ $item->quantity }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ $quotation->currency }} {{ number_format($item->unit_price, 2) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 text-right">
                                             @php
                                                $taxAmount = ($item->quantity * $item->unit_price) * ($item->tax_rate / 100);
                                                $lineTotal = ($item->quantity * $item->unit_price) + $taxAmount;
                                             @endphp
                                            {{ $quotation->currency }} {{ number_format($lineTotal, 2) }}
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
                                <span class="font-semibold text-gray-900">{{ $quotation->currency }} {{ number_format($quotation->subtotal, 2) }}</span>
                            </div>
                            
                            @if($quotation->igst > 0)
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-gray-600">IGST</span>
                                    <span class="text-gray-900">{{ $quotation->currency }} {{ number_format($quotation->igst, 2) }}</span>
                                </div>
                            @elseif($quotation->cgst > 0 || $quotation->sgst > 0)
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-gray-600">CGST</span>
                                    <span class="text-gray-900">{{ $quotation->currency }} {{ number_format($quotation->cgst, 2) }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-gray-600">SGST</span>
                                    <span class="text-gray-900">{{ $quotation->currency }} {{ number_format($quotation->sgst, 2) }}</span>
                                </div>
                            @endif

                            <div class="flex justify-between py-2 border-t-2 border-gray-200 mt-2">
                                <span class="font-bold text-lg text-gray-800">Total</span>
                                <span class="font-bold text-lg text-gray-800">{{ $quotation->currency }} {{ number_format($quotation->total, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Info & Terms -->
                    <div class="mt-12 pt-8 border-t border-gray-100">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Notes -->
                            @if($quotation->client_notes)
                            <div>
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Notes</h4>
                                <div class="text-sm text-gray-600 bg-gray-50 p-4 rounded-lg border border-gray-100 italic whitespace-pre-line">
                                    {{ $quotation->client_notes }}
                                </div>
                            </div>
                            @endif

                            <!-- Terms & Conditions -->
                            @if($quotation->terms_conditions)
                            <div>
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Terms & Conditions</h4>
                                <div class="text-sm text-gray-600 bg-gray-50 p-4 rounded-lg border border-gray-100 whitespace-pre-line">
                                    {{ $quotation->terms_conditions }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

            <!-- Revisions & Actions View -->
            @if(Auth::user()->isCompanyAdmin())
            <div x-show="activeView === 'actions'" style="display: none;" class="bg-white shadow-md rounded-lg overflow-hidden no-print mb-8">
                <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-900">Revisions & Actions</h3>
                </div>
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row gap-4 mb-8">
                        @if($quotation->status === 'approved' && !$quotation->isConverted())
                            <form action="{{ route('quotations.convert', $quotation) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full justify-center inline-flex items-center px-4 py-3 bg-green-600 text-white rounded-md font-bold hover:bg-green-700 shadow-sm transition">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Convert to Invoice
                                </button>
                            </form>
                        @endif
                        
                        @if(!$isLocked && !$quotation->isConverted())
                            <a href="{{ route('quotations.create', ['source_id' => $quotation->id]) }}" class="flex-1 w-full justify-center inline-flex items-center px-4 py-3 bg-blue-600 text-white rounded-md font-bold hover:bg-blue-700 shadow-sm transition outline-none">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                Create Sub-Revision
                            </a>
                        @elseif($isLocked)
                            <div class="flex-1 bg-gray-50 border border-dashed border-gray-300 rounded-lg p-3 text-center">
                                <span class="text-gray-500 text-xs font-medium italic">History locked - Version approved</span>
                            </div>
                        @endif
                    </div>

                    @if($revisions->count() > 1)
                    <div class="mt-8 border-t border-gray-100 pt-8">
                        <h4 class="font-bold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Version Timeline
                        </h4>
                        <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Version</th>
                                        <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-4 py-3 text-right font-bold text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-4 py-3 text-center font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-4 py-3 text-right"></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @foreach($revisions as $rev)
                                        <tr class="{{ $rev->id === $quotation->id ? 'bg-indigo-50/50 ring-1 ring-inset ring-indigo-200' : 'hover:bg-gray-50 transition' }}">
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <span class="font-mono font-bold {{ $rev->id === $quotation->id ? 'text-indigo-600' : 'text-gray-700' }}">V{{ $rev->revision_number }}</span>
                                                    @if($rev->id === $quotation->id)
                                                        <span class="ml-2 px-2 py-0.5 rounded text-[10px] bg-blue-100 text-blue-700 font-bold uppercase tracking-tighter">Viewing</span>
                                                    @endif
                                                    @if($rev->is_active)
                                                        <span class="ml-2 px-2 py-0.5 rounded text-[10px] bg-green-100 text-green-700 font-bold uppercase tracking-tighter ring-1 ring-green-200">Active</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-gray-500">
                                                {{ $rev->quotation_date->format('M j, Y') }}
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-right font-medium text-gray-900">
                                                {{ $rev->currency }} {{ number_format($rev->total, 2) }}
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                                <span class="px-2 py-1 text-[10px] font-bold uppercase rounded-full 
                                                    {{ $rev->status === 'approved' ? 'bg-green-100 text-green-700' : 
                                                      ($rev->status === 'draft' ? 'bg-gray-100 text-gray-600' : 'bg-blue-100 text-blue-700') }}">
                                                    {{ $rev->status }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-right font-medium">
                                                <div class="flex items-center justify-end space-x-3">
                                                    @if(!$isLocked && !$rev->is_active && Auth::user()->isCompanyAdmin())
                                                        <form action="{{ route('quotations.mark-as-active', $rev) }}" method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit" class="text-[10px] font-bold text-gray-400 hover:text-green-600 transition uppercase tracking-widest border border-gray-200 px-2 py-1 rounded hover:border-green-200 hover:bg-green-50" title="Mark as Active Revision">
                                                                Set Active
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @if($rev->id !== $quotation->id)
                                                        <a href="{{ route('quotations.show', $rev) }}" class="text-indigo-600 hover:text-indigo-900 font-bold text-xs uppercase tracking-tight">View &rarr;</a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    @if($quotation->isConverted())
                        <div class="px-6 py-4 bg-gray-50 border border-gray-200 rounded-lg text-center mb-8 inline-block w-full mt-8">
                            <span class="font-bold text-gray-700 block mb-1">Converted to Invoice</span>
                            <a href="{{ route('invoices.show', $quotation->invoice_id) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Invoice #{{ $quotation->invoice->invoice_number ?? 'Unknown' }} &rarr;</a>
                        </div>
                    @endif

                    @if($quotation->parent_id)
                        <div class="border-t border-gray-100 pt-6 mt-8 hidden">
                            <h4 class="font-bold text-gray-500 text-xs uppercase tracking-wide mb-4">Tree Navigation</h4>
                            <a href="{{ route('quotations.show', $quotation->parent_id) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 shadow-sm transition">
                                &larr; Return to Parent Revision
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Notes View -->
            @if(Auth::user()->isCompanyAdmin())
            <div x-show="activeView === 'notes'" style="display: none;" class="bg-white shadow-md rounded-lg overflow-hidden no-print mb-8">
                <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-900">Internal History & Notes</h3>
                </div>
                <div class="p-6">
                    @if($quotation->notes->count() > 0)
                        <div class="space-y-4 mb-8 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-gray-200 before:to-transparent">
                            @foreach($quotation->notes as $note)
                                <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full border border-white bg-indigo-100 text-indigo-600 font-bold shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow-sm z-10">
                                        {{ substr($note->user->name, 0, 1) }}
                                    </div>
                                    <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded-lg bg-white border border-gray-100 shadow-sm">
                                        <div class="flex flex-col mb-1">
                                            <span class="font-bold text-gray-900 text-sm">{{ $note->user->name }}</span>
                                            <span class="text-xs text-gray-500">{{ $note->created_at->format('M d, Y h:i A') }}</span>
                                        </div>
                                        <div class="text-gray-700 text-sm mt-2 whitespace-pre-line break-words">{{ $note->note }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-10 bg-gray-50 rounded-lg mb-8 border border-dashed border-gray-200">
                            <p class="text-gray-500 italic">No internal notes added yet.</p>
                        </div>
                    @endif

                    <div class="bg-indigo-50 p-5 rounded-xl border border-indigo-100">
                        <h4 class="font-bold text-indigo-900 mb-3">Add New Note</h4>
                        <form action="{{ route('quotations.notes.store', $quotation) }}" method="POST">
                            @csrf
                            <textarea name="note" rows="3" class="block w-full text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm" placeholder="Enter status changes, client feedback, or internal memos..." required></textarea>
                            <div class="mt-4 text-right">
                                <button class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 shadow-sm transition ease-in-out duration-150">
                                    Save Note
                                </button>
                            </div>
                        </form>
                    </div>
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
                        $company = $quotation->user->company ?? \App\Models\Company::first();
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
            #quotation-print-area, #quotation-print-area * {
                visibility: visible;
            }
            #quotation-print-area {
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
