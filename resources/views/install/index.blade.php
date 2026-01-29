<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Installation - The Higglers Invoicing</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-100">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div class="w-full sm:max-w-2xl mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                <div class="mb-8 text-center">
                    <h2 class="text-2xl font-bold text-gray-900">Welcome to The Higglers Invoicing</h2>
                    <p class="text-gray-600 mt-2">Please setup your company and admin account to get started.</p>
                </div>

                <form method="POST" action="{{ route('install.store') }}" enctype="multipart/form-data">
                    @csrf

                    <!-- Section: Company Details -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Company Details</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Company Name -->
                            <div>
                                <x-input-label for="company_name" :value="__('Company Name')" />
                                <x-text-input id="company_name" class="block mt-1 w-full" type="text" name="company_name" :value="old('company_name')" required autofocus />
                                <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
                            </div>

                            <!-- Company Logo -->
                            <div>
                                <x-input-label for="company_logo" :value="__('Company Logo (Optional)')" />
                                <input id="company_logo" type="file" name="company_logo" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none mt-1">
                                <p class="mt-1 text-sm text-gray-500">PNG, JPG or JPEG (Max 2MB).</p>
                                <x-input-error :messages="$errors->get('company_logo')" class="mt-2" />
                            </div>

                            <!-- GST Number -->
                            <div>
                                <x-input-label for="gst_number" :value="__('GST Number (Optional)')" />
                                <x-text-input id="gst_number" class="block mt-1 w-full" type="text" name="gst_number" :value="old('gst_number')" />
                                <x-input-error :messages="$errors->get('gst_number')" class="mt-2" />
                            </div>

                             <!-- Company Email -->
                             <div>
                                <x-input-label for="company_email" :value="__('Company Email')" />
                                <x-text-input id="company_email" class="block mt-1 w-full" type="email" name="company_email" :value="old('company_email')" />
                                <x-input-error :messages="$errors->get('company_email')" class="mt-2" />
                            </div>

                             <!-- Company Phone -->
                             <div>
                                <x-input-label for="company_phone" :value="__('Company Phone')" />
                                <x-text-input id="company_phone" class="block mt-1 w-full" type="text" name="company_phone" :value="old('company_phone')" />
                                <x-input-error :messages="$errors->get('company_phone')" class="mt-2" />
                            </div>

                            <!-- Address -->
                            <div class="col-span-1 md:col-span-2">
                                <x-input-label for="company_address" :value="__('Address')" />
                                <textarea id="company_address" name="company_address" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3" required>{{ old('company_address') }}</textarea>
                                <x-input-error :messages="$errors->get('company_address')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <!-- Section: Admin User -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Super Admin Account</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Name -->
                            <div>
                                <x-input-label for="name" :value="__('Admin Name')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <!-- Email -->
                            <div>
                                <x-input-label for="email" :value="__('Admin Email')" />
                                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <!-- Password -->
                            <div>
                                <x-input-label for="password" :value="__('Password')" />
                                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required />
                                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-8">
                        <x-primary-button>
                            {{ __('Complete Installation') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </body>
</html>
