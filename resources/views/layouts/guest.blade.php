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
    <body class="auth-body antialiased">
        <div class="auth-shell">
            <section class="auth-hero">
                <div class="auth-hero__badge">Secure LGU Administration</div>
                <h1>Government Beneficiary Management System</h1>
                <p>Admin-only access for citizen records, QR-enabled identification cards, barangay analytics, audit logs, and reporting.</p>

                <div class="auth-hero__stats">
                    <article>
                        <strong>Role-Based</strong>
                        <span>Super admin and staff admin controls</span>
                    </article>
                    <article>
                        <strong>Secure Data</strong>
                        <span>Hashed passwords, validated uploads, tracked sessions</span>
                    </article>
                    <article>
                        <strong>Operational</strong>
                        <span>Exports, ID cards, QR verification, and backups</span>
                    </article>
                </div>
            </section>

            <section class="auth-panel">
                <div class="auth-panel__brand">
                    <span class="sidebar-brand__logo auth-logo">
                        <x-application-logo class="h-11 w-11" />
                    </span>
                    <div class="sidebar-brand__copy">
                        <x-system-wordmark class="system-wordmark system-wordmark--auth" />
                        <p class="eyebrow">Administrator Sign In</p>

                    </div>
                </div>

                {{ $slot }}
            </section>
        </div>
    </body>
</html>
