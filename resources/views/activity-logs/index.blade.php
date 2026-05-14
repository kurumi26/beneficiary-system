<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="eyebrow">Audit Trail</p>
            <h1 class="page-title">Administrative activity logs</h1>
        </div>
    </x-slot>

    <section class="page-shell space-y-6">
        <form class="panel-grid panel-grid--filters" method="GET" action="{{ route('activity-logs.index') }}">
            <input class="form-input" name="search" placeholder="Search action or description" value="{{ $filters['search'] ?? '' }}">
            <select class="form-input" name="user_id">
                <option value="">All Admins</option>
                @foreach ($admins as $admin)
                    <option value="{{ $admin->id }}" @selected(($filters['user_id'] ?? '') == $admin->id)>{{ $admin->name }}</option>
                @endforeach
            </select>
            <div class="flex flex-col gap-2 sm:flex-row">
                <button class="button-primary" type="submit">Filter Logs</button>
                <a class="button-secondary" href="{{ route('activity-logs.index') }}">Reset</a>
            </div>
        </form>

        <article class="panel-card">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Admin</th>
                            <th>Action</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td>{{ $log->created_at->format('M d, Y h:i A') }}</td>
                                <td>{{ $log->user?->name ?? 'System' }}</td>
                                <td>{{ $log->action }}</td>
                                <td>{{ $log->description }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="empty-state">No activity logs available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="table-pagination">{{ $logs->links() }}</div>
        </article>
    </section>
</x-app-layout>
