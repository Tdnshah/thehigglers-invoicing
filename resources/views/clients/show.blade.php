<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Client Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">{{ $client->name }}</h3>
                        <div>
                            @if(!$client->clientUser)
                                <a href="{{ route('clients.user.create', $client) }}" class="text-green-600 hover:text-green-900 mr-3 font-semibold">Create Portal Access</a>
                            @else
                                <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full mr-3">Portal Access Active</span>
                            @endif
                            <a href="{{ route('clients.edit', $client) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                            <a href="{{ route('clients.index') }}" class="text-gray-600 hover:text-gray-900">Back</a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <h4 class="font-bold text-gray-700 mb-2">Contact Info</h4>
                            <p><strong>Email:</strong> {{ $client->email }}</p>
                            <p><strong>Phone:</strong> {{ $client->phone }}</p>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-700 mb-2">Business Details</h4>
                            <p><strong>GST Number:</strong> {{ $client->gst_number ?? 'N/A' }}</p>
                            <p><strong>Address:</strong><br>{{ $client->address }}</p>
                        </div>
                    </div>

                    <div class="border-t pt-6">
                        <h4 class="font-bold text-gray-700 mb-4">Invoices</h4>
                        @if($client->invoices->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Number</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($client->invoices as $invoice)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $invoice->invoice_number }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $invoice->invoice_date->format('d M, Y') }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : 
                                                      ($invoice->status === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                                        {{ ucfirst($invoice->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <a href="{{ route('invoices.show', $invoice) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-gray-500">No invoices found for this client.</p>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
