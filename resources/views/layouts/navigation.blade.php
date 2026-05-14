<div class="sidebar-backdrop" x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"></div>

<aside class="admin-sidebar" :class="sidebarOpen ? 'is-open' : ''">
    <div class="sidebar-brand">
        <a href="{{ route('dashboard') }}" class="sidebar-brand__link">
            <span class="sidebar-brand__logo">
                <x-application-logo class="h-10 w-10" />
            </span>
            <span class="sidebar-brand__copy">
                <x-system-wordmark class="system-wordmark" />
                <span class="sidebar-brand__eyebrow">Pili, Camarines Sur Capitol</span>
            </span>
        </a>

        <button class="sidebar-close" type="button" @click="sidebarOpen = false">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div class="sidebar-section">
        <p class="sidebar-section__label">Operations</p>
        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" @class(['sidebar-link', 'sidebar-link--active' => request()->routeIs('dashboard')])>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12 12 3l9 9M5 10v10h14V10"/></svg>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('beneficiaries.index') }}" @class(['sidebar-link', 'sidebar-link--active' => request()->routeIs('beneficiaries.*')])>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm13 10v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Beneficiaries</span>
            </a>

            <a href="{{ route('qr-scanner.index') }}" @class(['sidebar-link', 'sidebar-link--active' => request()->routeIs('qr-scanner.*')])>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8V6a2 2 0 0 1 2-2h2M20 8V6a2 2 0 0 0-2-2h-2M4 16v2a2 2 0 0 0 2 2h2M20 16v2a2 2 0 0 1-2 2h-2M8 12h8"/></svg>
                <span>QR Scanner</span>
            </a>
            <a href="{{ route('backups.index') }}" @class(['sidebar-link', 'sidebar-link--active' => request()->routeIs('backups.*')])>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                <span>Backups</span>
            </a>
                      <a href="{{ route('activity-logs.index') }}" @class(['sidebar-link', 'sidebar-link--active' => request()->routeIs('activity-logs.*')])>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12h4l3 8 4-16 3 8h4"/></svg>
                <span>Audit Logs</span>
            </a>
            <a href="{{ route('profile.edit') }}" @class(['sidebar-link', 'sidebar-link--active' => request()->routeIs('profile.*')])>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm8 9a8 8 0 1 0-16 0"/></svg>
                <span>Profile</span>
            </a>
        </nav>
    </div>



    <div class="sidebar-user">
        <div class="sidebar-user__avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
        <div>
            <strong>{{ auth()->user()->name }}</strong>
            <p>{{ auth()->user()->email }}</p>
        </div>
    </div>

    <div class="sidebar-mobile-actions md:hidden">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="button-secondary w-full" type="submit">Log Out</button>
        </form>
    </div>
</aside>
