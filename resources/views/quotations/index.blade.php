<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Quotations') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Your Quotations</h3>
                        @if(Auth::user()->isCompanyAdmin())
                        <a href="{{ route('quotations.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Create New Quotation
                        </a>
                        @endif
                    </div>

                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quotation #</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($quotations as $quotation)
                                    <!-- Parent Quotation Row -->
                                    <tr class="bg-white">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <a href="{{ route('quotations.show', $quotation) }}" class="text-indigo-600 hover:text-indigo-900 font-bold">
                                                {{ $quotation->quotation_number }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $quotation->client->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($quotation->quotation_date)->format('M d, Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">{{ $quotation->currency }} {{ number_format($quotation->total, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @php
                                                $statusClasses = [
                                                    'draft' => 'bg-gray-100 text-gray-800',
                                                    'sent' => 'bg-blue-100 text-blue-800',
                                                    'approved' => 'bg-green-100 text-green-800',
                                                    'rejected' => 'bg-red-100 text-red-800',
                                                ];
                                                $statusClass = $statusClasses[$quotation->status] ?? 'bg-gray-100 text-gray-800';
                                            @endphp
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                                {{ ucfirst($quotation->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('quotations.show', $quotation) }}" class="text-gray-600 hover:text-gray-900 mr-3">View</a>
                                            
                                            @php 
                                                $isTreeLocked = $quotation->status === 'approved' || $quotation->revisions->contains('status', 'approved');
                                            @endphp

                                            @if(Auth::user()->isCompanyAdmin() && !$isTreeLocked)
                                            <a href="{{ route('quotations.edit', $quotation) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                            <form action="{{ route('quotations.destroy', $quotation) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this quotation?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- Revisions (Tree view) -->
                                    @foreach($quotation->revisions as $revision)
                                        <tr class="bg-gray-50 border-t border-gray-100">
                                            <td class="px-6 py-2 whitespace-nowrap text-sm font-medium text-gray-700 pl-12 flex items-center">
                                                <span class="text-gray-400 mr-2">└─</span>
                                                <a href="{{ route('quotations.show', $revision) }}" class="text-indigo-500 hover:text-indigo-800">
                                                    {{ $revision->quotation_number }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-400">{{ $revision->client->name }}</td>
                                            <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-400">{{ \Carbon\Carbon::parse($revision->quotation_date)->format('M d, Y') }}</td>
                                            <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-500">{{ $revision->currency }} {{ number_format($revision->total, 2) }}</td>
                                            <td class="px-6 py-2 whitespace-nowrap text-sm">
                                                @php
                                                    $revStatusClass = $statusClasses[$revision->status] ?? 'bg-gray-100 text-gray-800';
                                                @endphp
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $revStatusClass }}">
                                                    {{ ucfirst($revision->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-2 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('quotations.show', $revision) }}" class="text-gray-500 hover:text-gray-800 mr-3">View</a>
                                                
                                                @if(Auth::user()->isCompanyAdmin() && !$isTreeLocked)
                                                <a href="{{ route('quotations.edit', $revision) }}" class="text-indigo-400 hover:text-indigo-700 mr-3">Edit</a>
                                                <form action="{{ route('quotations.destroy', $revision) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this revision?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-400 hover:text-red-700">Delete</button>
                                                </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No quotations found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $quotations->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
