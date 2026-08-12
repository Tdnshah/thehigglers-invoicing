@php
    /**
     * Place of supply and LUT, shared by the invoice and quotation forms.
     *
     * These two fields are one rule: a domestic document carries a place of
     * supply, an export carries the LUT it is zero-rated under. Keeping them in
     * one partial is what stops the two forms drifting apart.
     *
     * Expects:
     *   $document   Invoice|Quotation|null  null on a create form
     *   $lutOptions Collection<array{label:string,value:string}>
     *   $typeVar    string  the Alpine variable holding the document type
     *   $kind       string  'invoice' | 'quotation'
     */
    $kind = $kind ?? 'invoice';
    $typeVar = $typeVar ?? 'invoiceType';
    $lutOptions = $lutOptions ?? collect();
    $selectedState = old('place_of_supply', $document->place_of_supply ?? null);
    $selectedLut = old('lut_number', $document->lut_number ?? null);
@endphp

<!-- Place of Supply -->
<div x-show="{{ $typeVar }} !== 'export'">
    <x-input-label for="place_of_supply" :value="__('Place of Supply')" />
    <select id="place_of_supply" name="place_of_supply"
            class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
        <option value="">Select a State</option>
        @foreach(config('gst-states.states') as $code => $state)
            {{-- Numeric state codes come back from config as ints, so compare as strings. --}}
            {{-- Retired codes stay selectable only on a document that already carries one. --}}
            @continue(in_array((string) $code, config('gst-states.deprecated'), true) && (string) $code !== (string) $selectedState)
            <option value="{{ $code }}" {{ (string) $code === (string) $selectedState ? 'selected' : '' }}>{{ $state }} ({{ $code }})</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('place_of_supply')" class="mt-2" />
</div>

<!-- LUT Number -->
<div x-show="{{ $typeVar }} === 'export'">
    <x-input-label for="lut_number" :value="__('LUT Number')" />
    @if($lutOptions->isNotEmpty())
        <select id="lut_number" name="lut_number"
                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="">Select a LUT</option>
            @foreach($lutOptions as $lut)
                <option value="{{ $lut['value'] }}" {{ $selectedLut === $lut['value'] ? 'selected' : '' }}>{{ $lut['label'] }} ({{ $lut['value'] }})</option>
            @endforeach
            {{-- Keep a LUT that is no longer in company settings selectable, so editing does not silently drop it. --}}
            @if(filled($selectedLut) && ! $lutOptions->contains('value', $selectedLut))
                <option value="{{ $selectedLut }}" selected>{{ $selectedLut }} (not in company settings)</option>
            @endif
        </select>
    @else
        <x-text-input id="lut_number" class="block mt-1 w-full" type="text" name="lut_number" :value="$selectedLut" />
        <p class="mt-1 text-xs text-gray-500">
            Add your LUT numbers as custom fields in
            <a href="{{ route('settings.show', 'custom-fields') }}" class="font-semibold underline">Settings</a> to pick them from a list here.
        </p>
    @endif
    <x-input-error :messages="$errors->get('lut_number')" class="mt-2" />
</div>
