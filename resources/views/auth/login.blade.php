<x-guest-layout>
    <x-auth-session-status class="status-banner mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label class="auth-label" for="email">Email Address</label>
            <input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            @error('email') <p class="form-error mt-2">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="auth-label" for="password">Password</label>
            <input id="password" class="auth-input" type="password" name="password" required autocomplete="current-password">
            @error('password') <p class="form-error mt-2">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-between gap-4">
            <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-slate-600">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-sky-700 shadow-sm focus:ring-sky-300" name="remember">
                <span>Keep this admin session active</span>
            </label>

            <span class="auth-chip">Admin Only</span>
        </div>

        <div class="flex items-center justify-between gap-3 pt-2">
            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-sky-800 underline-offset-4 hover:underline" href="{{ route('password.request') }}">
                    Forgot your password?
                </a>
            @endif

            <button class="button-primary" type="submit">Access Admin Panel</button>
        </div>
    </form>
</x-guest-layout>
