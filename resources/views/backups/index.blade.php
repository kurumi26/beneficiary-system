<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="eyebrow">System Maintenance</p>
                <h1 class="page-title">Backup and restore</h1>
            </div>

            <form class="w-full sm:w-auto" method="POST" action="{{ route('backups.store') }}">
                @csrf
                <button class="button-primary w-full sm:w-auto" type="submit">Create Backup</button>
            </form>
        </div>
    </x-slot>

    <section class="page-shell space-y-6">
        @if (session('status'))
            <div class="status-banner">{{ session('status') }}</div>
        @endif

        <article class="panel-card">
            <h2 class="section-title">Restore backup</h2>
            <p class="section-copy mt-1">Only super admins can restore data snapshots.</p>
            <form class="mt-4 flex flex-col gap-3 md:flex-row" method="POST" action="{{ route('backups.restore') }}" enctype="multipart/form-data">
                @csrf
                <input class="form-input" type="file" name="backup_file" accept=".json" required>
                <button class="button-secondary w-full md:w-auto" type="submit">Restore Uploaded Backup</button>
            </form>
        </article>

        <article class="panel-card">
            <h2 class="section-title">Backup history</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>File</th>
                            <th>Size</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($files as $file)
                            <tr>
                                <td>{{ $file['name'] }}</td>
                                <td>{{ number_format($file['size'] / 1024, 1) }} KB</td>
                                <td>{{ \Carbon\Carbon::createFromTimestamp($file['last_modified'])->format('M d, Y h:i A') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="empty-state">No backups created yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>
</x-app-layout>
