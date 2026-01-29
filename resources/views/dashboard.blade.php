<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <!-- Status Cards -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm font-medium uppercase">Draft Invoices</div>
                    <div class="text-3xl font-bold text-gray-800 mt-2">{{ $statusCounts['draft'] ?? 0 }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-blue-500 text-sm font-medium uppercase">Sent Invoices</div>
                    <div class="text-3xl font-bold text-blue-600 mt-2">{{ $statusCounts['sent'] ?? 0 }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-green-500 text-sm font-medium uppercase">Paid Invoices</div>
                    <div class="text-3xl font-bold text-green-600 mt-2">{{ $statusCounts['paid'] ?? 0 }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-red-500 text-sm font-medium uppercase">Overdue Invoices</div>
                    <div class="text-3xl font-bold text-red-600 mt-2">{{ $statusCounts['overdue'] ?? 0 }}</div>
                </div>
            </div>

            <!-- Financials Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Outstanding -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-700 mb-4">Outstanding Amount (Sent + Overdue)</h3>
                    @if(count($outstanding) > 0)
                        <ul class="space-y-3">
                            @foreach($outstanding as $item)
                                <li class="flex justify-between items-center border-b pb-2 last:border-0">
                                    <span class="font-semibold text-gray-600">{{ $item->currency }}</span>
                                    <span class="text-xl font-bold text-gray-800">{{ number_format($item->total_amount, 2) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-gray-500 italic">No outstanding invoices.</p>
                    @endif
                </div>

                <!-- Earnings -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-700 mb-4">Total Earnings (Paid)</h3>
                    @if(count($earnings) > 0)
                        <ul class="space-y-3">
                            @foreach($earnings as $item)
                                <li class="flex justify-between items-center border-b pb-2 last:border-0">
                                    <span class="font-semibold text-gray-600">{{ $item->currency }}</span>
                                    <span class="text-xl font-bold text-green-600">{{ number_format($item->total_amount, 2) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-gray-500 italic">No payments received yet.</p>
                    @endif
                </div>
            </div>

            <!-- Recent Invoices -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-700">Recent Invoices</h3>
                        <a href="{{ route('invoices.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">View All</a>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 bg-gray-50"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($recentInvoices as $invoice)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $invoice->invoice_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $invoice->client->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $invoice->invoice_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-bold">
                                            {{ $invoice->currency }} {{ number_format($invoice->total, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : 
                                                  ($invoice->status === 'overdue' ? 'bg-red-100 text-red-800' : 
                                                  ($invoice->status === 'sent' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800')) }}">
                                                {{ ucfirst($invoice->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('invoices.show', $invoice) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No invoices found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
