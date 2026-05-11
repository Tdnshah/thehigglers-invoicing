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

@push('scripts')
@if(isset($companyCustomFields) && $companyCustomFields->isNotEmpty())
<script>window.__cfAvailable = {!! json_encode($companyCustomFields->values()->toArray()) !!};</script>
@endif
<script>
function customFieldsPicker(available, existing) {
    return {
        available,
        selected: Array.isArray(existing) && existing.length > 0
            ? existing.map(f => ({ key: f.key, value: f.value ?? '' }))
            : [],
        get canAddMore() {
            const used = this.selected.map(f => f.key).filter(Boolean);
            return this.available.some(opt => !used.includes(opt.key));
        },
        optionsFor(index) {
            const used = this.selected.map((f, i) => i !== index ? f.key : null).filter(k => k);
            return this.available.filter(opt => !used.includes(opt.key));
        },
        onKeyChange(index) {
            const key = this.selected[index].key;
            const def = this.available.find(opt => opt.key === key);
            if (def) this.selected[index].value = def.value || '';
        },
        addField() { this.selected.push({ key: '', value: '' }); },
        removeField(index) { this.selected.splice(index, 1); },
    };
}
</script>
@endpush

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Quotation') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="quotationForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('quotations.store') }}">
                        @csrf
                        @if(isset($sourceQuotation))
                            <input type="hidden" name="parent_id" value="{{ $sourceQuotation->parent_id ?? $sourceQuotation->id }}">
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Client -->
                            <div>
                                <x-input-label for="client_id" :value="__('Client')" />
                                @if(isset($sourceQuotation))
                                    <div class="mt-1 p-3 bg-gray-50 border border-gray-100 rounded-md text-gray-800 font-bold flex items-center justify-between">
                                        <span>{{ $sourceQuotation->client->name }}</span>
                                        <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400 bg-gray-200 px-2 py-0.5 rounded">Locked for Revision</span>
                                        <input type="hidden" name="client_id" value="{{ $sourceQuotation->client_id }}">
                                    </div>
                                @else
                                    <select id="client_id" name="client_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required x-on:change="updateCurrency($event.target.value)">
                                        <option value="">Select a Client</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}" data-currency="{{ $client->currency ?? 'INR' }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                                {{ $client->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                                <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
                            </div>

                            <!-- Quotation Number -->
                            <div>
                                <x-input-label for="quotation_number" :value="__('Quotation Number')" />
                                <x-text-input id="quotation_number" class="block mt-1 w-full" type="text" name="quotation_number" :value="old('quotation_number')" placeholder="Leave blank to auto-generate" />
                                <x-input-error :messages="$errors->get('quotation_number')" class="mt-2" />
                            </div>

                            <!-- Quotation Date -->
                            <div>
                                <x-input-label for="quotation_date" :value="__('Quotation Date')" />
                                <x-text-input id="quotation_date" class="block mt-1 w-full" type="date" name="quotation_date" :value="old('quotation_date', date('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('quotation_date')" class="mt-2" />
                            </div>

                            <!-- Valid Until -->
                            <div>
                                <x-input-label for="valid_until" :value="__('Valid Until')" />
                                <x-text-input id="valid_until" class="block mt-1 w-full" type="date" name="valid_until" :value="old('valid_until')" />
                                <x-input-error :messages="$errors->get('valid_until')" class="mt-2" />
                            </div>

                            <!-- Quotation Type -->
                            <div>
                                <x-input-label for="quotation_type" :value="__('Quotation Type')" />
                                @if(isset($sourceQuotation))
                                    <div class="mt-1 p-3 bg-gray-50 border border-gray-100 rounded-md text-gray-800 font-bold flex items-center justify-between">
                                        <span>{{ ucfirst($sourceQuotation->quotation_type) }}</span>
                                        <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400 bg-gray-200 px-2 py-0.5 rounded">Fixed</span>
                                        <input type="hidden" name="quotation_type" value="{{ $sourceQuotation->quotation_type }}">
                                    </div>
                                @else
                                    <select id="quotation_type" name="quotation_type" x-model="quotationType" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        <option value="regular">Regular</option>
                                        <option value="export">Export</option>
                                        <option value="interstate">Interstate</option>
                                    </select>
                                @endif
                                <x-input-error :messages="$errors->get('quotation_type')" class="mt-2" />
                            </div>

                            <!-- Place of Supply -->
                            <div x-show="quotationType !== 'export'">
                                <x-input-label for="place_of_supply" :value="__('Place of Supply (State Code)')" />
                                <x-text-input id="place_of_supply" class="block mt-1 w-full" type="text" name="place_of_supply" :value="old('place_of_supply')" placeholder="e.g. 27" maxlength="2" />
                                <x-input-error :messages="$errors->get('place_of_supply')" class="mt-2" />
                            </div>

                            <!-- LUT Number -->
                            <div x-show="quotationType === 'export'">
                                <x-input-label for="lut_number" :value="__('LUT Number')" />
                                <x-text-input id="lut_number" class="block mt-1 w-full" type="text" name="lut_number" :value="old('lut_number')" />
                                <x-input-error :messages="$errors->get('lut_number')" class="mt-2" />
                            </div>

                            <!-- Currency -->
                            <!-- Currency -->
                            <div>
                                <x-input-label for="currency" :value="__('Currency')" />
                                @if(isset($sourceQuotation))
                                    <div class="mt-1 p-3 bg-gray-50 border border-gray-100 rounded-md text-gray-800 font-bold flex items-center justify-between">
                                        <span>{{ $sourceQuotation->currency }}</span>
                                        <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400 bg-gray-200 px-2 py-0.5 rounded">Fixed</span>
                                        <input type="hidden" name="currency" value="{{ $sourceQuotation->currency }}">
                                    </div>
                                @else
                                    <select id="currency" name="currency" x-model="currency" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                        <option value="INR">INR (₹)</option>
                                        <option value="USD">USD ($)</option>
                                        <option value="EUR">EUR (€)</option>
                                        <option value="GBP">GBP (£)</option>
                                    </select>
                                @endif
                                <x-input-error :messages="$errors->get('currency')" class="mt-2" />
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
                                                        <option value="0">0%</option>
                                                        <option value="5">5%</option>
                                                        <option value="12">12%</option>
                                                        <option value="18">18%</option>
                                                        <option value="28">28%</option>
                                                    </select>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <input type="number" :name="'items[' + index + '][quantity]'" x-model="item.quantity" step="0.01" min="0" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <input type="number" :name="'items[' + index + '][unit_price]'" x-model="item.unit_price" step="0.01" min="0" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <span class="block w-full py-2 px-3 text-sm text-gray-700 bg-gray-50 rounded-md" x-text="calculateItemTotal(item).toFixed(2)"></span>
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
                                    <span class="font-bold text-gray-900" x-text="calculateSubtotal().toFixed(2)"></span>
                                </div>
                                <div class="flex justify-between text-sm" x-show="calculateTotalTax() > 0">
                                    <span class="font-medium text-gray-600">Tax:</span>
                                    <span class="font-bold text-gray-900" x-text="calculateTotalTax().toFixed(2)"></span>
                                </div>
                                <div class="flex justify-between text-sm border-t pt-2 mt-2">
                                    <span class="font-bold text-gray-800">Grand Total:</span>
                                    <span class="font-bold text-gray-900" x-text="calculateGrandTotal().toFixed(2)"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Custom Fields -->
                        @if(isset($companyCustomFields) && $companyCustomFields->isNotEmpty())
                        @php
                            $__cfExisting = collect(old('custom_fields', isset($sourceQuotation) ? ($sourceQuotation->custom_fields ?? []) : []))
                                ->filter(fn($f) => !empty($f['key']))
                                ->map(fn($f) => ['key' => $f['key'], 'value' => $f['value'] ?? ''])
                                ->values()->toArray();
                        @endphp
                        <script>window.__cfExisting = {!! json_encode($__cfExisting) !!};</script>
                        <div class="mb-6"
                             x-data="customFieldsPicker(window.__cfAvailable, window.__cfExisting)">
                            <div class="flex justify-between items-center mb-3">
                                <h3 class="text-lg font-medium text-gray-900">Custom Fields</h3>
                                <button type="button" @click="addField()" x-show="canAddMore"
                                        class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">
                                    + Add Field
                                </button>
                            </div>
                            <p x-show="selected.length === 0" class="text-sm text-gray-400 italic mb-2">
                                Click "+ Add Field" to attach a custom field to this quotation.
                            </p>
                            <template x-for="(field, index) in selected" :key="index">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-2/5">
                                        <select :name="'custom_fields[' + index + '][key]'"
                                                x-model="field.key"
                                                @change="onKeyChange(index)"
                                                class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                            <option value="">— Select Field —</option>
                                            <template x-for="opt in optionsFor(index)" :key="opt.key">
                                                <option :value="opt.key" x-text="opt.key"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="flex-1">
                                        <input type="text"
                                               :name="'custom_fields[' + index + '][value]'"
                                               x-model="field.value"
                                               readonly
                                               class="block w-full border-gray-300 bg-gray-50 rounded-md shadow-sm text-sm cursor-default"
                                               placeholder="Auto-filled from company settings">
                                    </div>
                                    <button type="button" @click="removeField(index)"
                                            class="text-red-500 hover:text-red-700 flex-shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                        @endif

                        <!-- Notes -->
                        <div class="mb-6">
                            <x-input-label for="client_notes" :value="__('Notes (Visible to Client)')" />
                            <textarea id="client_notes" name="client_notes" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3">{{ old('client_notes', isset($sourceQuotation) ? $sourceQuotation->client_notes : '') }}</textarea>
                            <x-input-error :messages="$errors->get('client_notes')" class="mt-2" />
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="mb-6">
                            <x-input-label for="terms_conditions" :value="__('Terms and Conditions')" />
                            <textarea id="terms_conditions" name="terms_conditions" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3">{{ old('terms_conditions', isset($sourceQuotation) ? $sourceQuotation->terms_conditions : '') }}</textarea>
                            <x-input-error :messages="$errors->get('terms_conditions')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('quotations.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Cancel</a>
                            <x-primary-button>
                                {{ __('Create Quotation') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function quotationForm() {
            @if(isset($sourceQuotation))
                @php
                    $sourceItems = $sourceQuotation->items->map(function($item) {
                        return [
                            'description' => $item->description,
                            'hsn_code' => (string)$item->hsn_code,
                            'tax_rate' => (float)$item->tax_rate,
                            'quantity' => (float)$item->quantity,
                            'unit_price' => (float)$item->unit_price
                        ];
                    })->toArray();
                @endphp
                const initialData = {
                    quotationType: "{{ old('quotation_type', $sourceQuotation->quotation_type) }}",
                    currency: "{{ old('currency', $sourceQuotation->currency) }}",
                    items: @json($sourceItems)
                };
            @else
                const initialData = {
                    quotationType: "{{ old('quotation_type', 'regular') }}",
                    currency: "{{ old('currency', 'INR') }}",
                    items: [
                        { description: '', quantity: 1, unit_price: 0, hsn_code: '', tax_rate: 18 }
                    ]
                };
            @endif

            return {
                ...initialData,
                updateCurrency(clientId) {
                    if (!clientId) return;
                    // Find selected option to get data attribute
                    const select = document.getElementById('client_id');
                    const option = select.options[select.selectedIndex];
                    const clientCurrency = option.getAttribute('data-currency');
                    if (clientCurrency) {
                        this.currency = clientCurrency;
                    }
                },
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
