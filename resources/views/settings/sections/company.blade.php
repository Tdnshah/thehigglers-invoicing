<div class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">

    <div class="sm:col-span-2">
        <label for="name" class="block text-sm font-medium text-foreground">Company name</label>
        <input id="name" name="name" type="text" required value="{{ old('name', $company->name) }}"
               class="mt-1.5 block w-full rounded-md border-input bg-background text-sm shadow-xs placeholder:text-muted-foreground focus:border-ring focus:ring-1 focus:ring-ring">
        <p class="mt-1.5 text-xs text-muted-foreground">Printed at the top of every document when no logo is uploaded.</p>
        @error('name')<p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="gst_number" class="block text-sm font-medium text-foreground">GSTIN</label>
        <input id="gst_number" name="gst_number" type="text" value="{{ old('gst_number', $company->gst_number) }}"
               class="mt-1.5 block w-full rounded-md border-input bg-background font-mono text-sm uppercase shadow-xs focus:border-ring focus:ring-1 focus:ring-ring">
        @error('gst_number')<p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="website" class="block text-sm font-medium text-foreground">Website</label>
        <input id="website" name="website" type="text" value="{{ old('website', $company->website) }}"
               class="mt-1.5 block w-full rounded-md border-input bg-background text-sm shadow-xs focus:border-ring focus:ring-1 focus:ring-ring">
        @error('website')<p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-foreground">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $company->email) }}"
               class="mt-1.5 block w-full rounded-md border-input bg-background text-sm shadow-xs focus:border-ring focus:ring-1 focus:ring-ring">
        @error('email')<p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="phone" class="block text-sm font-medium text-foreground">Phone</label>
        <input id="phone" name="phone" type="text" value="{{ old('phone', $company->phone) }}"
               class="mt-1.5 block w-full rounded-md border-input bg-background text-sm shadow-xs focus:border-ring focus:ring-1 focus:ring-ring">
        @error('phone')<p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label for="address" class="block text-sm font-medium text-foreground">Registered address</label>
        <textarea id="address" name="address" rows="3" required
                  class="mt-1.5 block w-full rounded-md border-input bg-background text-sm shadow-xs focus:border-ring focus:ring-1 focus:ring-ring">{{ old('address', $company->address) }}</textarea>
        @error('address')<p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label for="company_logo" class="block text-sm font-medium text-foreground">Logo</label>
        <div class="mt-1.5 flex flex-wrap items-center gap-4">
            <div class="flex h-16 w-32 shrink-0 items-center justify-center rounded-md border border-dashed border-border bg-muted/50">
                @if($company->logo_path)
                    <img src="{{ asset('storage/' . $company->logo_path) }}" alt="Current logo" class="max-h-14 max-w-[7rem] object-contain">
                @else
                    <span class="text-xs text-muted-foreground">No logo</span>
                @endif
            </div>
            <div class="min-w-0 flex-1">
                <input id="company_logo" name="company_logo" type="file" accept="image/*"
                       class="block w-full text-sm text-foreground file:mr-3 file:rounded-md file:border-0 file:bg-secondary file:px-3 file:py-2 file:text-sm file:font-medium file:text-secondary-foreground hover:file:bg-accent">
                <p class="mt-1.5 text-xs text-muted-foreground">PNG, JPG or WEBP up to 2 MB. When a logo is set it replaces the company name on documents.</p>
            </div>
        </div>
        @error('company_logo')<p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>@enderror
    </div>
</div>
