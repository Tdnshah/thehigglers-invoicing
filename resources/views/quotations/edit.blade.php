@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<style>
    .ql-container { font-size: 13px; }
    .ql-toolbar.ql-snow { padding: 4px; border-radius: 6px 6px 0 0; }
    .ql-container.ql-snow { border-radius: 0 0 6px 6px; min-height: 80px; background: #fff; }
    .ql-editor { min-height: 75px; padding: 6px 10px; }
    .ql-editor ol, .ql-editor ul { padding-left: 1.2em; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
@endpush

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Quotation') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="quotationForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('quotations.update', $quotation) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Client -->
                            <div>
                                <x-input-label for="client_id" :value="__('Client')" />
                                <select id="client_id" name="client_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">Select a Client</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ old('client_id', $quotation->client_id) == $client->id ? 'selected' : '' }}>
                                            {{ $client->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
                            </div>

                            <!-- Quotation Number -->
                            <div>
                                <x-input-label for="quotation_number" :value="__('Quotation Number')" />
                                <x-text-input id="quotation_number" class="block mt-1 w-full" type="text" name="quotation_number" :value="old('quotation_number', $quotation->quotation_number)" required />
                                <x-input-error :messages="$errors->get('quotation_number')" class="mt-2" />
                            </div>

                            <!-- Quotation Date -->
                            <div>
                                <x-input-label for="quotation_date" :value="__('Quotation Date')" />
                                <x-text-input id="quotation_date" class="block mt-1 w-full" type="date" name="quotation_date" :value="old('quotation_date', $quotation->quotation_date->format('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('quotation_date')" class="mt-2" />
                            </div>

                            <!-- Valid Until -->
                            <div>
                                <x-input-label for="valid_until" :value="__('Valid Until')" />
                                <x-text-input id="valid_until" class="block mt-1 w-full" type="date" name="valid_until" :value="old('valid_until', optional($quotation->valid_until)->format('Y-m-d'))" />
                                <x-input-error :messages="$errors->get('valid_until')" class="mt-2" />
                            </div>

                            <!-- Quotation Type -->
                            <div>
                                <x-input-label for="quotation_type" :value="__('Quotation Type')" />
                                <select id="quotation_type" name="quotation_type" x-model="quotationType" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="regular" {{ old('quotation_type', $quotation->quotation_type) == 'regular' ? 'selected' : '' }}>Regular</option>
                                    <option value="export" {{ old('quotation_type', $quotation->quotation_type) == 'export' ? 'selected' : '' }}>Export</option>
                                    <option value="interstate" {{ old('quotation_type', $quotation->quotation_type) == 'interstate' ? 'selected' : '' }}>Interstate</option>
                                </select>
                                <x-input-error :messages="$errors->get('quotation_type')" class="mt-2" />
                            </div>

                            <!-- Place of Supply -->
                            <div x-show="quotationType !== 'export'">
                                <x-input-label for="place_of_supply" :value="__('Place of Supply (State Code)')" />
                                <x-text-input id="place_of_supply" class="block mt-1 w-full" type="text" name="place_of_supply" :value="old('place_of_supply', $quotation->place_of_supply)" placeholder="e.g. 27" maxlength="2" />
                                <x-input-error :messages="$errors->get('place_of_supply')" class="mt-2" />
                            </div>

                            <!-- LUT Number -->
                            <div x-show="quotationType === 'export'">
                                <x-input-label for="lut_number" :value="__('LUT Number')" />
                                <x-text-input id="lut_number" class="block mt-1 w-full" type="text" name="lut_number" :value="old('lut_number', $quotation->lut_number)" />
                                <x-input-error :messages="$errors->get('lut_number')" class="mt-2" />
                            </div>

                            <!-- Currency -->
                            <div>
                                <x-input-label for="currency" :value="__('Currency')" />
                                <select id="currency" name="currency" x-model="currency" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="INR">INR (₹)</option>
                                    <option value="USD">USD ($)</option>
                                    <option value="EUR">EUR (€)</option>
                                    <option value="GBP">GBP (£)</option>
                                </select>
                                <x-input-error :messages="$errors->get('currency')" class="mt-2" />
                            </div>

                            <!-- Status -->
                            <div>
                                <x-input-label for="status" :value="__('Status')" />
                                <select id="status" name="status" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="draft" {{ old('status', $quotation->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="sent" {{ old('status', $quotation->status) == 'sent' ? 'selected' : '' }}>Sent</option>
                                    <option value="approved" {{ old('status', $quotation->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ old('status', $quotation->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                                <x-input-error :messages="$errors->get('status')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Quotation Items -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Items</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/3">Description</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">HSN/SAC</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Tax %</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Qty</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Price</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Total</th>
                                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-16"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template x-for="(item, index) in items" :key="index">
                                            <tr>
                                                <td class="px-4 py-2 align-top" style="min-width:260px;">
                                                    <div
                                                        x-init="
                                                            const _q = new Quill($el, {
                                                                theme: 'snow',
                                                                modules: { toolbar: [['bold','italic'],[{list:'ordered'},{list:'bullet'}],['clean']] }
                                                            });
                                                            if (item.description) _q.root.innerHTML = item.description;
                                                            _q.on('text-change', () => {
                                                                item.description = _q.root.innerHTML === '<p><br></p>' ? '' : _q.root.innerHTML;
                                                            });
                                                        "
                                                    ></div>
                                                    <input type="hidden" :name="'items[' + index + '][description]'" :value="item.description">
                                                </td>
                                                <td class="px-4 py-2">
                                                    <input type="text" :name="'items[' + index + '][hsn_code]'" x-model="item.hsn_code" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" placeholder="HSN">
                                                </td>
                                                <td class="px-4 py-2">
                                                    <select :name="'items[' + index + '][tax_rate]'" x-model="item.tax_rate" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                                        <option :value="0">0%</option>
                                                        <option :value="5">5%</option>
                                                        <option :value="12">12%</option>
                                                        <option :value="18">18%</option>
                                                        <option :value="28">28%</option>
                                                    </select>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <input type="number" :name="'items[' + index + '][quantity]'" x-model="item.quantity" step="0.01" min="0" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <div class="relative">
                                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500" x-text="currency === 'INR' ? '₹' : (currency === 'USD' ? '$' : (currency === 'EUR' ? '€' : (currency === 'GBP' ? '£' : currency)))"></span>
                                                        <input type="number" :name="'items[' + index + '][unit_price]'" x-model="item.unit_price" step="0.01" min="0" class="block w-full pl-8 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <span class="block w-full py-2 px-3 text-sm text-gray-700 bg-gray-50 rounded-md">
                                                        <span x-text="currency === 'INR' ? '₹' : (currency === 'USD' ? '$' : (currency === 'EUR' ? '€' : (currency === 'GBP' ? '£' : currency)))"></span> 
                                                        <span x-text="calculateItemTotal(item).toFixed(2)"></span>
                                                    </span>
                                                </td>
                                                <td class="px-4 py-2 text-right">
                                                    <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-900" x-show="items.length > 1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5" class="px-4 py-2">
                                                <button type="button" @click="addItem()" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">
                                                    + Add Item
                                                </button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Totals -->
                        <div class="flex justify-end mb-6">
                            <div class="w-64 space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="font-medium text-gray-600">Subtotal:</span>
                                    <span class="font-bold text-gray-900">
                                        <span x-text="currency === 'INR' ? '₹' : (currency === 'USD' ? '$' : (currency === 'EUR' ? '€' : (currency === 'GBP' ? '£' : currency)))"></span>
                                        <span x-text="calculateSubtotal().toFixed(2)"></span>
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm" x-show="calculateTotalTax() > 0">
                                    <span class="font-medium text-gray-600">Tax:</span>
                                    <span class="font-bold text-gray-900">
                                        <span x-text="currency === 'INR' ? '₹' : (currency === 'USD' ? '$' : (currency === 'EUR' ? '€' : (currency === 'GBP' ? '£' : currency)))"></span>
                                        <span x-text="calculateTotalTax().toFixed(2)"></span>
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm border-t pt-2 mt-2">
                                    <span class="font-bold text-gray-800">Grand Total:</span>
                                    <span class="font-bold text-gray-900">
                                        <span x-text="currency === 'INR' ? '₹' : (currency === 'USD' ? '$' : (currency === 'EUR' ? '€' : (currency === 'GBP' ? '£' : currency)))"></span>
                                        <span x-text="calculateGrandTotal().toFixed(2)"></span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-6">
                            <x-input-label for="client_notes" :value="__('Notes (Visible to Client)')" />
                            <textarea id="client_notes" name="client_notes" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3">{{ old('client_notes', $quotation->client_notes) }}</textarea>
                            <x-input-error :messages="$errors->get('client_notes')" class="mt-2" />
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="mb-6">
                            <x-input-label for="terms_conditions" :value="__('Terms and Conditions')" />
                            <textarea id="terms_conditions" name="terms_conditions" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3">{{ old('terms_conditions', $quotation->terms_conditions) }}</textarea>
                            <x-input-error :messages="$errors->get('terms_conditions')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('quotations.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Cancel</a>
                            <x-primary-button>
                                {{ __('Update Quotation') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function quotationForm() {
            return {
                quotationType: "{{ old('quotation_type', $quotation->quotation_type ?? 'regular') }}",
                currency: "{{ old('currency', $quotation->currency ?? 'INR') }}",
                items: @json($quotationItems),
                addItem() {
                    this.items.push({ description: '', quantity: 1, unit_price: 0, hsn_code: '', tax_rate: 18 });
                },
                removeItem(index) {
                    this.items.splice(index, 1);
                },
                calculateSubtotal() {
                    return this.items.reduce((sum, item) => {
                        return sum + (parseFloat(item.quantity) * parseFloat(item.unit_price) || 0);
                    }, 0);
                },
                calculateItemTotal(item) {
                    let baseAmount = (parseFloat(item.quantity) * parseFloat(item.unit_price) || 0);
                    let taxAmount = baseAmount * (parseFloat(item.tax_rate) / 100);
                    return baseAmount + taxAmount;
                },
                calculateTotalTax() {
                    return this.items.reduce((sum, item) => {
                         let baseAmount = (parseFloat(item.quantity) * parseFloat(item.unit_price) || 0);
                         return sum + (baseAmount * (parseFloat(item.tax_rate) / 100));
                    }, 0);
                },
                calculateGrandTotal() {
                    return this.calculateSubtotal() + this.calculateTotalTax();
                }
            }
        }
    </script>
</x-app-layout>
