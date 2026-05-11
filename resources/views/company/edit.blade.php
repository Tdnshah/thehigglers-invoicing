<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Company Settings') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="companyForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('company.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Basic Details -->
                            <div class="col-span-1 md:col-span-2">
                                <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Basic Information</h3>
                            </div>

                            <div>
                                <x-input-label for="name" :value="__('Company Name')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $company->name)" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="gst_number" :value="__('GST Number')" />
                                <x-text-input id="gst_number" class="block mt-1 w-full" type="text" name="gst_number" :value="old('gst_number', $company->gst_number)" />
                                <x-input-error :messages="$errors->get('gst_number')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $company->email)" />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="phone" :value="__('Phone')" />
                                <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone', $company->phone)" />
                                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                            </div>

                             <!-- Address -->
                             <div class="col-span-1 md:col-span-2">
                                <x-input-label for="address" :value="__('Address')" />
                                <textarea id="address" name="address" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3" required>{{ old('address', $company->address) }}</textarea>
                                <x-input-error :messages="$errors->get('address')" class="mt-2" />
                            </div>

                             <!-- Logo -->
                             <div class="col-span-1 md:col-span-2">
                                <x-input-label for="company_logo" :value="__('Company Logo')" />
                                @if($company->logo_path)
                                    <div class="mt-2 mb-2">
                                        <img src="{{ asset('storage/' . $company->logo_path) }}" alt="Current Logo" class="h-20 w-auto object-contain border p-1 rounded">
                                    </div>
                                @endif
                                <input id="company_logo" type="file" name="company_logo" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none mt-1">
                                <p class="mt-1 text-sm text-gray-500">Upload to replace. Max 2MB.</p>
                                <x-input-error :messages="$errors->get('company_logo')" class="mt-2" />
                            </div>

                            <!-- Bank Details -->
                            <div class="col-span-1 md:col-span-2 mt-4">
                                <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Bank Details (For Invoices)</h3>
                            </div>

                            <div>
                                <x-input-label for="bank_name" :value="__('Bank Name')" />
                                <x-text-input id="bank_name" class="block mt-1 w-full" type="text" name="bank_name" :value="old('bank_name', $company->bank_name)" />
                                <x-input-error :messages="$errors->get('bank_name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="bank_account_number" :value="__('Account Number')" />
                                <x-text-input id="bank_account_number" class="block mt-1 w-full" type="text" name="bank_account_number" :value="old('bank_account_number', $company->bank_account_number)" />
                                <x-input-error :messages="$errors->get('bank_account_number')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="bank_ifsc" :value="__('IFSC Code')" />
                                <x-text-input id="bank_ifsc" class="block mt-1 w-full" type="text" name="bank_ifsc" :value="old('bank_ifsc', $company->bank_ifsc)" />
                                <x-input-error :messages="$errors->get('bank_ifsc')" class="mt-2" />
                            </div>

                            <!-- Custom Fields -->
                            <div class="col-span-1 md:col-span-2 mt-4">
                                <div class="flex justify-between items-center border-b pb-2 mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">Custom Fields</h3>
                                    <button type="button" @click="addCustomField()" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">
                                        + Add Field
                                    </button>
                                </div>
                                <div class="space-y-3">
                                    <template x-for="(field, index) in customFields" :key="index">
                                        <div class="flex flex-wrap items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                            <div class="flex-1 min-w-[160px]">
                                                <label class="block text-xs text-gray-500 mb-1">Label</label>
                                                <input type="text" :name="'custom_fields[' + index + '][key]'" x-model="field.key" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" placeholder="e.g. LUT Number 2024-25">
                                            </div>
                                            <div class="flex-1 min-w-[160px]">
                                                <label class="block text-xs text-gray-500 mb-1">Default Value</label>
                                                <input type="text" :name="'custom_fields[' + index + '][value]'" x-model="field.value" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" placeholder="Default value (optional)">
                                            </div>
                                            <div class="flex flex-col items-center gap-1">
                                                <label class="text-xs text-gray-500">Show in Invoice</label>
                                                <input type="hidden" :name="'custom_fields[' + index + '][show_in_invoice]'" value="0">
                                                <input type="checkbox" :name="'custom_fields[' + index + '][show_in_invoice]'" x-model="field.show_in_invoice" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                            </div>
                                            <div class="flex flex-col items-center gap-1">
                                                <label class="text-xs text-gray-500">Show in Quotation</label>
                                                <input type="hidden" :name="'custom_fields[' + index + '][show_in_quotation]'" value="0">
                                                <input type="checkbox" :name="'custom_fields[' + index + '][show_in_quotation]'" x-model="field.show_in_quotation" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                            </div>
                                            <div class="flex items-end pb-1">
                                                <button type="button" @click="removeCustomField(index)" class="text-red-600 hover:text-red-900">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                    <div x-show="customFields.length === 0" class="text-gray-500 text-sm italic">
                                        No custom fields added. Add fields like 'LUT Number' or 'Tax ID' if needed.
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="flex items-center justify-end mt-8">
                            <x-primary-button>
                                {{ __('Save Settings') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @php
        $customFieldsJs = collect($company->custom_fields ?? [])->map(function($field, $key) {
            // New format: array of objects with 'key' property
            if (is_array($field) && array_key_exists('key', $field)) {
                return [
                    'key' => $field['key'] ?? '',
                    'value' => $field['value'] ?? '',
                    'show_in_invoice' => isset($field['show_in_invoice']) ? (bool) $field['show_in_invoice'] : true,
                    'show_in_quotation' => isset($field['show_in_quotation']) ? (bool) $field['show_in_quotation'] : true,
                ];
            }
            // Legacy format: ['Label' => 'Value']
            return [
                'key' => is_string($key) ? $key : '',
                'value' => is_string($field) ? $field : '',
                'show_in_invoice' => true,
                'show_in_quotation' => true,
            ];
        })->values()->toArray();
    @endphp
    <script>
        function companyForm() {
            return {
                customFields: @json($customFieldsJs),
                addCustomField() {
                    this.customFields.push({ key: '', value: '', show_in_invoice: true, show_in_quotation: true });
                },
                removeCustomField(index) {
                    this.customFields.splice(index, 1);
                }
            }
        }
    </script>
</x-app-layout>
