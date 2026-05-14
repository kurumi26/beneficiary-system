<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="eyebrow">Beneficiary Records</p>
                <h1 class="page-title">Manage citizen and assistance profiles</h1>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                <a class="button-secondary" href="{{ route('beneficiaries.export', ['format' => 'pdf'] + request()->query()) }}">Export PDF</a>
                <a class="button-secondary" href="{{ route('beneficiaries.export', ['format' => 'excel'] + request()->query()) }}">Export Excel</a>
                <a class="button-primary" href="{{ route('beneficiaries.create') }}">Register Beneficiary</a>
            </div>
        </div>
    </x-slot>

    @php
        $activeFilterCount = collect([
            $filters['search'] ?? null,
            $filters['barangay'] ?? null,
            $filters['status'] ?? null,
            $filters['gender'] ?? null,
            $filters['category'] ?? null,
            $filters['age_group'] ?? null,
            $filters['registered_from'] ?? null,
            $filters['registered_to'] ?? null,
            ($filters['sort'] ?? 'newest') !== 'newest' ? ($filters['sort'] ?? null) : null,
        ])->filter(fn ($value) => filled($value))->count();

        $hasAdvancedFilters = collect([
            $filters['barangay'] ?? null,
            $filters['status'] ?? null,
            $filters['gender'] ?? null,
            $filters['category'] ?? null,
            $filters['age_group'] ?? null,
            $filters['registered_from'] ?? null,
            $filters['registered_to'] ?? null,
            ($filters['sort'] ?? 'newest') !== 'newest' ? ($filters['sort'] ?? null) : null,
        ])->contains(fn ($value) => filled($value));
    @endphp

    <section class="page-shell space-y-6">
        <form
            x-data="{ showAdvanced: @js($hasAdvancedFilters) }"
            class="panel-grid panel-grid--filters"
            method="GET"
            action="{{ route('beneficiaries.index') }}"
        >
            <div class="md:col-span-full">
                <div class="flex flex-col gap-3 rounded-[1.5rem] border px-4 py-4 md:flex-row md:items-center md:justify-between" style="border-color: rgba(15, 76, 129, 0.08); background: rgba(255, 255, 255, 0.78);">
                    <div>
                        <p class="eyebrow">Quick Filters</p>
                        <p class="section-copy mt-1">
                            @if ($activeFilterCount > 0)
                                {{ $activeFilterCount }} active {{ $activeFilterCount === 1 ? 'filter' : 'filters' }}. Refine only when needed.
                            @else
                                Search first, then open more filters only when needed.
                            @endif
                        </p>
                    </div>

                    <button class="button-secondary shrink-0" type="button" @click="showAdvanced = !showAdvanced">
                        <span x-show="!showAdvanced">More Filters</span>
                        <span x-show="showAdvanced" x-cloak>Hide Filters</span>
                    </button>
                </div>
            </div>

            <div class="md:col-span-full">
                <input class="form-input" name="search" placeholder="Search by name, number, address, barangay" value="{{ $filters['search'] ?? '' }}">
            </div>

            <div class="md:contents" x-cloak x-show="showAdvanced">
                <select class="form-input" name="barangay">
                    <option value="">All Barangays</option>
                    @foreach ($barangays as $barangay)
                        <option value="{{ $barangay }}" @selected(($filters['barangay'] ?? '') === $barangay)>{{ $barangay }}</option>
                    @endforeach
                </select>
                <select class="form-input" name="status">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <select class="form-input" name="gender">
                    <option value="">All Genders</option>
                    @foreach ($genders as $gender)
                        <option value="{{ $gender }}" @selected(($filters['gender'] ?? '') === $gender)>{{ $gender }}</option>
                    @endforeach
                </select>
                <select class="form-input" name="category">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ $category }}</option>
                    @endforeach
                </select>
                <select class="form-input" name="age_group">
                    <option value="">All Age Groups</option>
                    @foreach ($ageGroups as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['age_group'] ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <input class="form-input" type="date" name="registered_from" value="{{ $filters['registered_from'] ?? '' }}">
                <input class="form-input" type="date" name="registered_to" value="{{ $filters['registered_to'] ?? '' }}">
                <select class="form-input" name="sort">
                    <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>Newest</option>
                    <option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>Oldest</option>
                    <option value="alphabetical" @selected(($filters['sort'] ?? '') === 'alphabetical')>Alphabetical</option>
                    <option value="barangay" @selected(($filters['sort'] ?? '') === 'barangay')>Barangay</option>
                </select>
            </div>

            <div class="md:col-span-full flex flex-col gap-2 sm:flex-row">
                <button class="button-primary" type="submit">Apply Filters</button>
                <a class="button-secondary" href="{{ route('beneficiaries.index') }}">Reset</a>
            </div>
        </form>

        <form method="POST" action="{{ route('beneficiaries.bulk') }}" class="panel-card overflow-hidden">
            @csrf
            <div class="table-toolbar">
                <div>
                    <h2 class="section-title">Registered beneficiaries</h2>
                    <p class="section-copy">Bulk print, export, or deactivate selected records.</p>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <select class="form-input" name="action" required>
                        <option value="">Bulk Action</option>
                        <option value="deactivate">Deactivate</option>
                        <option value="export_csv">Export CSV</option>
                        <option value="export_excel">Export Excel</option>
                        <option value="export_pdf">Export PDF</option>
                        <option value="print_id_cards">Print ID Cards</option>
                        <option value="export_qr">Export QR Codes</option>
                    </select>
                    <button class="button-primary" type="submit">Apply</button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" onclick="document.querySelectorAll('input[name=&quot;selected[]&quot;]').forEach((element) => element.checked = this.checked)"></th>
                            <th>Beneficiary ID</th>
                            <th>Name</th>
                            <th>Barangay</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Assistance Logs</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($beneficiaries as $beneficiary)
                            <tr>
                                <td><input type="checkbox" name="selected[]" value="{{ $beneficiary->id }}"></td>
                                <td>{{ $beneficiary->beneficiary_number }}</td>
                                <td>
                                    <div class="font-semibold text-slate-900">{{ $beneficiary->full_name }}</div>
                                    <div class="text-xs text-slate-500">{{ $beneficiary->gender }} · {{ $beneficiary->age() }} years old</div>
                                </td>
                                <td>{{ $beneficiary->barangay }}</td>
                                <td>{{ $beneficiary->category }}</td>
                                <td><span class="status-chip status-chip--{{ $beneficiary->status }}">{{ ucfirst($beneficiary->status) }}</span></td>
                                <td>{{ $beneficiary->assistance_logs_count }}</td>
                                <td class="text-right"><a class="table-link" href="{{ route('beneficiaries.show', $beneficiary) }}">View</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="empty-state">No beneficiary records match the current filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="table-pagination">{{ $beneficiaries->links() }}</div>
        </form>
    </section>
</x-app-layout>
