<x-guest-layout>
    <header>
        <h1 class="text-2xl font-bold tracking-tight text-foreground">Create your account</h1>
        <p class="mt-2 text-sm text-muted-foreground">A few details and you can start issuing documents.</p>
    </header>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-foreground">Full name</label>
            <input id="name" name="name" type="text" required autofocus autocomplete="name"
                   value="{{ old('name') }}" placeholder="Tejas Shah"
                   @class([
                       'mt-1.5 block h-10 w-full rounded-md bg-background px-3 text-sm shadow-xs placeholder:text-muted-foreground focus:ring-1',
                       'border-input focus:border-ring focus:ring-ring' => ! $errors->has('name'),
                       'border-destructive focus:border-destructive focus:ring-destructive' => $errors->has('name'),
                   ])>
            @error('name')<p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-foreground">Email</label>
            <input id="email" name="email" type="email" required autocomplete="username"
                   value="{{ old('email') }}" placeholder="you@company.com"
                   @class([
                       'mt-1.5 block h-10 w-full rounded-md bg-background px-3 text-sm shadow-xs placeholder:text-muted-foreground focus:ring-1',
                       'border-input focus:border-ring focus:ring-ring' => ! $errors->has('email'),
                       'border-destructive focus:border-destructive focus:ring-destructive' => $errors->has('email'),
                   ])>
            @error('email')<p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>@enderror
        </div>

        <div x-data="{ show: false }">
            <label for="password" class="block text-sm font-medium text-foreground">Password</label>
            <div class="relative mt-1.5">
                <input id="password" name="password" :type="show ? 'text' : 'password'" type="password" required autocomplete="new-password"
                       placeholder="At least 8 characters"
                       @class([
                           'block h-10 w-full rounded-md bg-background pl-3 pr-10 text-sm shadow-xs placeholder:text-muted-foreground focus:ring-1',
                           'border-input focus:border-ring focus:ring-ring' => ! $errors->has('password'),
                           'border-destructive focus:border-destructive focus:ring-destructive' => $errors->has('password'),
                       ])>
                <button type="button" @click="show = !show" tabindex="-1"
                        class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-muted-foreground transition-colors hover:text-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        :aria-label="show ? 'Hide password' : 'Show password'">
                    <svg x-show="!show" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    <svg x-show="show" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                    </svg>
                </button>
            </div>
            @error('password')<p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-foreground">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                   placeholder="Repeat your password"
                   class="mt-1.5 block h-10 w-full rounded-md border-input bg-background px-3 text-sm shadow-xs placeholder:text-muted-foreground focus:border-ring focus:ring-1 focus:ring-ring">
            @error('password_confirmation')<p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>@enderror
        </div>

        <button type="submit"
                class="inline-flex h-10 w-full items-center justify-center rounded-md bg-primary px-4 text-sm font-semibold text-primary-foreground shadow-xs transition-colors hover:bg-primary/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
            Create account
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-muted-foreground">
        Already have an account?
        <a href="{{ route('login') }}" class="font-medium text-primary transition-colors hover:text-primary/80 focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">Sign in</a>
    </p>
</x-guest-layout>
