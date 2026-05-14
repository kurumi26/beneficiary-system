<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="eyebrow">Beneficiary Profile</p>
                <h1 class="page-title">{{ $beneficiary->full_name }}</h1>
            </div>

            <div class="flex gap-2">
                <a class="button-secondary" href="{{ route('beneficiaries.id-card', $beneficiary) }}">Print ID Card</a>
                <a class="button-secondary" href="{{ route('beneficiaries.id-card.pdf', $beneficiary) }}">Export ID PDF</a>
                <a class="button-primary" href="{{ route('beneficiaries.edit', $beneficiary) }}">Edit Record</a>
            </div>
        </div>
    </x-slot>

    <section class="page-shell space-y-6">
        @if (session('status'))
            <div class="status-banner">{{ session('status') }}</div>
        @endif

        <div class="detail-grid">
            <article class="panel-card space-y-5">
                <div class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">
                    <div class="flex gap-4">
                        <div class="profile-photo">
                            @if ($beneficiary->photo_path)
                                <img src="{{ Storage::url($beneficiary->photo_path) }}" alt="{{ $beneficiary->full_name }} photo">
                            @else
                                <span>{{ strtoupper(substr($beneficiary->full_name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <div>
                            <div class="eyebrow">{{ $beneficiary->beneficiary_number }}</div>
                            <h2 class="section-title mt-2">{{ $beneficiary->full_name }}</h2>
                            <p class="section-copy">{{ $beneficiary->barangay }} · {{ $beneficiary->address }}</p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <span class="status-chip status-chip--{{ $beneficiary->status }}">{{ ucfirst($beneficiary->status) }}</span>
                                <span class="meta-chip">{{ $beneficiary->category }}</span>
                                <span class="meta-chip">{{ $beneficiary->civil_status }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="qr-panel">{!! $qrSvg !!}</div>
                </div>

                <dl class="detail-list">
                    <div><dt>Birthdate</dt><dd>{{ optional($beneficiary->birthdate)->format('M d, Y') }} ({{ $beneficiary->age() }} years old)</dd></div>
                    <div><dt>Gender</dt><dd>{{ $beneficiary->gender }}</dd></div>
                    <div><dt>Contact</dt><dd>{{ $beneficiary->contact_number ?: 'Not provided' }}</dd></div>
                    <div><dt>Date Issued</dt><dd>{{ optional($beneficiary->date_issued)->format('M d, Y') }}</dd></div>
                    <div><dt>Registered By</dt><dd>{{ $beneficiary->creator?->name ?? 'System' }}</dd></div>
                    <div><dt>Assistance Total</dt><dd>PHP {{ number_format($assistanceTotal, 2) }}</dd></div>
                </dl>

                @if ($beneficiary->notes)
                    <div class="note-block">
                        <h3 class="subsection-title">Notes</h3>
                        <p>{{ $beneficiary->notes }}</p>
                    </div>
                @endif

                <div class="flex flex-wrap gap-2">
                    @if ($beneficiary->status !== 'inactive' || ! $beneficiary->archived_at)
                        <form method="POST" action="{{ route('beneficiaries.archive', $beneficiary) }}">
                            @csrf
                            @method('PATCH')
                            <button class="button-secondary" type="submit">Archive Record</button>
                        </form>
                    @endif

                    @if (auth()->user()->isSuperAdmin())
                        <form method="POST" action="{{ route('beneficiaries.destroy', $beneficiary) }}" onsubmit="return confirm('Delete this record permanently?');">
                            @csrf
                            @method('DELETE')
                            <button class="button-danger" type="submit">Delete Record</button>
                        </form>
                    @endif
                </div>
            </article>

            <aside class="space-y-6">
                <article class="panel-card">
                    <h2 class="section-title">Supporting files</h2>
                    <div class="mt-4 space-y-3 text-sm text-slate-600">
                        @if ($beneficiary->valid_id_path)
                            <a class="table-link" href="{{ Storage::url($beneficiary->valid_id_path) }}" target="_blank">View uploaded valid ID</a>
                        @endif

                        @forelse ($beneficiary->documents as $document)
                            <a class="table-link block" href="{{ Storage::url($document->file_path) }}" target="_blank">{{ $document->title }}</a>
                        @empty
                            <p>No supporting documents uploaded yet.</p>
                        @endforelse
                    </div>
                </article>

                <article class="panel-card">
                    <h2 class="section-title">Log assistance</h2>
                    <form class="mt-4 space-y-3" method="POST" action="{{ route('beneficiaries.assistance-logs.store', $beneficiary) }}">
                        @csrf
                        <input class="form-input" name="assistance_type" placeholder="Assistance type" required>
                        <input class="form-input" type="number" step="0.01" name="amount" placeholder="Amount (optional)">
                        <input class="form-input" type="date" name="assisted_at" value="{{ now()->format('Y-m-d') }}" required>
                        <textarea class="form-input min-h-24" name="description" placeholder="Description"></textarea>
                        <button class="button-primary w-full" type="submit">Add Assistance Log</button>
                    </form>
                </article>
            </aside>
        </div>

        <div class="detail-grid">
            <article class="panel-card">
                <h2 class="section-title">Assistance history</h2>
                <div class="mt-4 overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Logged By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($beneficiary->assistanceLogs as $log)
                                <tr>
                                    <td>{{ optional($log->assisted_at)->format('M d, Y') }}</td>
                                    <td>{{ $log->assistance_type }}</td>
                                    <td>{{ $log->description ?: 'No description' }}</td>
                                    <td>PHP {{ number_format((float) $log->amount, 2) }}</td>
                                    <td>{{ $log->creator?->name ?? 'System' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="empty-state">No assistance records logged yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="panel-card">
                <h2 class="section-title">Activity history</h2>
                <ul class="timeline-list mt-4">
                    @forelse ($beneficiary->activityLogs as $log)
                        <li>
                            <div class="timeline-meta">{{ $log->created_at->format('M d, Y h:i A') }} · {{ $log->user?->name ?? 'System' }}</div>
                            <div class="timeline-copy">{{ $log->description }}</div>
                        </li>
                    @empty
                        <li class="timeline-copy">No activity history recorded yet.</li>
                    @endforelse
                </ul>
            </article>
        </div>
    </section>
</x-app-layout>
