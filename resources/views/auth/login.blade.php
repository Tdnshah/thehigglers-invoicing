<x-guest-layout>
    <header>
        <h1 class="text-2xl font-bold tracking-tight text-foreground">Sign in</h1>
        <p class="mt-2 text-sm text-muted-foreground">Welcome back. Enter your details to continue.</p>
    </header>

    <x-auth-session-status class="mt-6" :status="session('status')" />

    @if($errors->any())
        <div role="alert" class="mt-6 flex items-start gap-3 rounded-lg border border-destructive-muted bg-destructive-muted px-4 py-3">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-destructive-muted-foreground" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path>
            </svg>
            <p class="text-sm font-medium text-destructive-muted-foreground">{{ $errors->first() }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-foreground">Email</label>
            <input id="email" name="email" type="email" required autofocus autocomplete="username"
                   value="{{ old('email') }}" placeholder="you@company.com"
                   @class([
                       'mt-1.5 block h-10 w-full rounded-md bg-background px-3 text-sm shadow-xs placeholder:text-muted-foreground focus:ring-1',
                       'border-input focus:border-ring focus:ring-ring' => ! $errors->has('email'),
                       'border-destructive focus:border-destructive focus:ring-destructive' => $errors->has('email'),
                   ])>
        </div>

        <div>
            <div class="flex items-baseline justify-between">
                <label for="password" class="block text-sm font-medium text-foreground">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs font-medium text-primary transition-colors hover:text-primary/80 focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                        Forgot password?
                    </a>
                @endif
            </div>
            <div class="relative mt-1.5" x-data="{ show: false }">
                <input id="password" name="password" :type="show ? 'text' : 'password'" type="password" required autocomplete="current-password"
                       placeholder="••••••••"
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
        </div>

        <label for="remember_me" class="flex items-center gap-2">
            <input id="remember_me" name="remember" type="checkbox"
                   class="h-4 w-4 rounded border-input text-primary focus:ring-ring">
            <span class="text-sm text-muted-foreground">Keep me signed in</span>
        </label>

        <button type="submit"
                class="inline-flex h-10 w-full items-center justify-center rounded-md bg-primary px-4 text-sm font-semibold text-primary-foreground shadow-xs transition-colors hover:bg-primary/90 focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
            Sign in
        </button>
    </form>

    @if (Route::has('register'))
        <p class="mt-8 text-center text-sm text-muted-foreground">
            Need an account?
            <a href="{{ route('register') }}" class="font-medium text-primary transition-colors hover:text-primary/80 focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">Create one</a>
        </p>
    @endif
</x-guest-layout>
