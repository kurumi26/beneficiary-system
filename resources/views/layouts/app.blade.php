<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Government Beneficiary Management System') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|space-grotesk:500,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="app-body antialiased">
        <div x-data="{ sidebarOpen: false }" class="admin-shell">
            @include('layouts.navigation')

            <div class="admin-main">
                <header class="topbar">
                    <div class="flex items-center gap-3">
                        <button class="topbar-toggle" type="button" @click="sidebarOpen = true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <div>
                            <p class="topbar-label">Admin Control Panel</p>
                            <h2 class="topbar-title">{{ now()->format('F d, Y') }}</h2>
                        </div>
                    </div>

                    <div class="topbar-meta">
                        <div class="text-right">
                            <div class="font-semibold text-slate-900">{{ auth()->user()->name }}</div>
                            <div class="text-xs uppercase tracking-[0.2em] text-slate-500">{{ str_replace('_', ' ', auth()->user()->role) }}</div>
                        </div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="button-secondary" type="submit">Log Out</button>
                        </form>
                    </div>
                </header>

                @isset($header)
                    <header class="page-header">
                        {{ $header }}
                    </header>
                @endisset

                <main class="admin-content">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
