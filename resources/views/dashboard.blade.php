<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="eyebrow">Operational Overview</p>
            <h1 class="page-title">Government Beneficiary Dashboard</h1>
        </div>
    </x-slot>

    @php
        $totalBeneficiaries = $summary['totalBeneficiaries'];
        $coverageAreaCount = $coverageSnapshot->count();
        $latestBeneficiary = $recentBeneficiaries->first();
    @endphp

    <section class="page-shell space-y-6">
        <div class="overview-band">
            <article class="overview-feature">
                <div class="overview-feature__meta">
                    <div>
                        <p class="eyebrow text-white/80">Command Deck</p>
                        <h2>Beneficiary registry snapshot</h2>
                    </div>

                    <span class="overview-feature__badge">Admin-only analytics</span>
                </div>

                <div class="overview-feature__body">
                    <div class="overview-feature__summary">
                        <span class="overview-feature__value-label">Total registered beneficiaries</span>
                        <div class="overview-feature__value">{{ number_format($summary['totalBeneficiaries']) }}</div>
                        <p class="overview-feature__copy">Total registered beneficiaries currently tracked for municipal assistance, verification, and audit review.</p>

                        <div class="overview-feature__pills">
                            <span class="overview-feature__pill">
                                {{ $coverageAreaCount }} {{ $coverageAreaCount === 1 ? 'barangay covered' : 'barangays covered' }}
                            </span>
                            <span class="overview-feature__pill">Audit trail active</span>
                        </div>
                    </div>

                    <div class="overview-feature__insights">
                        <article class="overview-feature__insight">
                            <span>Registry Reach</span>
                            <strong>{{ $coverageAreaCount }} {{ $coverageAreaCount === 1 ? 'area' : 'areas' }}</strong>
                            <small>{{ $coverageAreaCount === 1 ? 'One barangay is represented in the current registry snapshot.' : 'Multiple barangays are represented in the current registry snapshot.' }}</small>
                        </article>

                        <article class="overview-feature__insight">
                            <span>Latest Registered</span>
                            <strong>{{ $latestBeneficiary?->full_name ?? 'No records yet' }}</strong>
                            <small>
                                @if ($latestBeneficiary?->created_at)
                                    Added on {{ $latestBeneficiary->created_at->format('M d, Y') }}
                                @else
                                    Waiting for the first beneficiary registration.
                                @endif
                            </small>
                        </article>
                    </div>
                </div>

                <div class="overview-feature__footer">
                    <span class="overview-feature__footer-label">Verification Flow</span>
                    <p>QR scanner linked. Generated beneficiary codes can be verified directly from the admin scanner module.</p>
                </div>
            </article>

            <div class="overview-metrics">
                <article class="metric-card metric-card--active">
                    <div class="metric-card__head">
                        <span class="metric-card__label">Active</span>
                        <span class="metric-card__dot"></span>
                    </div>
                    <div class="metric-card__value">{{ $summary['activeBeneficiaries'] }}</div>
                    <div class="metric-card__hint">Currently eligible and active for assistance workflows.</div>
                </article>

                <article class="metric-card metric-card--pending">
                    <div class="metric-card__head">
                        <span class="metric-card__label">Pending Review</span>
                        <span class="metric-card__dot"></span>
                    </div>
                    <div class="metric-card__value">{{ $summary['pendingBeneficiaries'] }}</div>
                    <div class="metric-card__hint">Records that still need confirmation or final approval.</div>
                </article>

                <article class="metric-card metric-card--inactive">
                    <div class="metric-card__head">
                        <span class="metric-card__label">Inactive</span>
                        <span class="metric-card__dot"></span>
                    </div>
                    <div class="metric-card__value">{{ $summary['inactiveBeneficiaries'] }}</div>
                    <div class="metric-card__hint">Archived, paused, or no longer active in the registry.</div>
                </article>

                <article class="metric-card metric-card--logs">
                    <div class="metric-card__head">
                        <span class="metric-card__label">Assistance Logs</span>
                        <span class="metric-card__dot"></span>
                    </div>
                    <div class="metric-card__value">{{ $summary['assistanceEvents'] }}</div>
                    <div class="metric-card__hint">Recorded assistance events already saved in the system.</div>
                </article>

                <article class="metric-card metric-card--wide metric-card--funds">
                    <div class="metric-card__head">
                        <span class="metric-card__label">Aid Released</span>
                        <span class="metric-card__dot"></span>
                    </div>
                    <div class="metric-card__value metric-card__value--money">PHP {{ number_format($summary['totalAssistanceAmount'], 2) }}</div>
                    <div class="metric-card__hint">Total recorded assistance value released across all logged support activity.</div>
                </article>
            </div>
        </div>

        <div class="dashboard-grid--analytics">
            <article class="panel-card">
                <div class="section-heading">
                    <div>
                        <h2 class="section-title">Registration trend</h2>
                        <p class="section-copy">Six-month intake movement for beneficiary registrations.</p>
                    </div>
                </div>
                <div class="chart-frame">
                    <canvas class="chart-surface" data-chart='@json($charts["registration"])'></canvas>
                </div>
            </article>

            <article class="panel-card">
                <h2 class="section-title">Status breakdown</h2>
                <p class="section-copy mt-2">Quick share of active, pending, and inactive records.</p>
                <div class="chart-frame chart-frame--compact">
                    <canvas class="chart-surface" data-chart='@json($charts["status"])'></canvas>
                </div>
            </article>
		</div>

        <div class="dashboard-grid--triple">
            <article class="panel-card">
                <h2 class="section-title">Gender distribution</h2>
                <p class="section-copy mt-2">Population split across encoded gender values.</p>
                <div class="chart-frame chart-frame--compact">
                    <canvas class="chart-surface" data-chart='@json($charts["gender"])'></canvas>
                </div>
            </article>

            <article class="panel-card">
                <h2 class="section-title">Age groups</h2>
                <p class="section-copy mt-2">Age-segment mix for faster targeting and planning.</p>
                <div class="chart-frame chart-frame--compact">
                    <canvas class="chart-surface" data-chart='@json($charts["age"])'></canvas>
                </div>
            </article>

            <article class="panel-card">
                <h2 class="section-title">Priority category mix</h2>
                <p class="section-copy mt-2">Senior citizens, PWD, and solo parents at a glance.</p>
                <div class="chart-frame chart-frame--compact">
                    <canvas class="chart-surface" data-chart='@json($charts["priority"])'></canvas>
                </div>
            </article>
        </div>

        <div class="dashboard-grid--split">
            <article class="panel-card">
                <h2 class="section-title">Beneficiary share by barangay</h2>
                <p class="section-copy mt-2">Largest coverage areas are grouped into a pie-style distribution.</p>
                <div class="chart-frame">
                    <canvas class="chart-surface" data-chart='@json($charts["barangay"])'></canvas>
                </div>
            </article>

            <article class="panel-card">
                <h2 class="section-title">Assistance allocation by barangay</h2>
                <p class="section-copy mt-2">Released assistance amounts across the highest-funded barangays.</p>
                <div class="chart-frame">
                    <canvas class="chart-surface" data-chart='@json($charts["assistance"])'></canvas>
                </div>
            </article>
        </div>

        <div class="dashboard-grid--triple">
            <article class="panel-card">
                <h2 class="section-title">Most assisted barangays</h2>
                <ul class="insight-list mt-4">
                    @forelse ($mostAssistedBarangays as $barangay)
                        <li>
                            <span>{{ $barangay['barangay'] }}</span>
                            <strong>{{ $barangay['total'] }} logs · PHP {{ number_format($barangay['amount'], 2) }}</strong>
                        </li>
                    @empty
                        <li><span>No assistance logs yet.</span><strong>0</strong></li>
                    @endforelse
                </ul>
            </article>

            <article class="panel-card">
                <h2 class="section-title">Coverage snapshot</h2>
                <ul class="insight-list mt-4">
                    @forelse ($coverageSnapshot as $barangay => $percentage)
                        <li><span>{{ $barangay }}</span><strong>{{ $percentage }}%</strong></li>
                    @empty
                        <li><span>No data yet.</span><strong>0%</strong></li>
                    @endforelse
                </ul>
            </article>

            <article class="panel-card">
                <h2 class="section-title">Recently registered</h2>
                <ul class="insight-list mt-4">
                    @forelse ($recentBeneficiaries as $beneficiary)
                        <li>
                            <span>{{ $beneficiary->full_name }} · {{ $beneficiary->barangay }}</span>
                            <strong>{{ $beneficiary->created_at->format('M d') }}</strong>
                        </li>
                    @empty
                        <li><span>No registrations yet.</span><strong>--</strong></li>
                    @endforelse
                </ul>
            </article>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.querySelectorAll('[data-chart]').forEach((element) => {
            const isCompactViewport = window.matchMedia('(max-width: 767px)').matches;
            const configuration = JSON.parse(element.dataset.chart);
            const data = configuration.data ?? {
                labels: configuration.labels ?? [],
                datasets: configuration.datasets ?? [],
            };
            const options = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: isCompactViewport ? 8 : 10,
                            padding: isCompactViewport ? 12 : 18,
                            font: {
                                size: isCompactViewport ? 10 : 12,
                            },
                        },
                    },
                    ...(configuration.options?.plugins ?? {}),
                },
                ...configuration.options,
            };

            new Chart(element, {
                ...configuration,
                data,
                options,
            });
        });
    </script>
</x-app-layout>
