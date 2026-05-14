<?php

namespace App\Http\Controllers;

use App\Models\AssistanceLog;
use App\Models\Beneficiary;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $beneficiaries = Beneficiary::query()->whereNull('archived_at')->get();
        $assistanceLogs = AssistanceLog::query()->with('beneficiary:id,barangay')->get();

        $summary = [
            'totalBeneficiaries' => $beneficiaries->count(),
            'activeBeneficiaries' => $beneficiaries->where('status', Beneficiary::STATUS_ACTIVE)->count(),
            'inactiveBeneficiaries' => $beneficiaries->where('status', Beneficiary::STATUS_INACTIVE)->count(),
            'pendingBeneficiaries' => $beneficiaries->where('status', Beneficiary::STATUS_PENDING)->count(),
            'assistanceEvents' => $assistanceLogs->count(),
            'totalAssistanceAmount' => (float) $assistanceLogs->sum('amount'),
        ];

        $perBarangay = $beneficiaries
            ->groupBy('barangay')
            ->map->count()
            ->sortDesc();

        $monthlyLabels = collect(range(5, 0))
            ->map(fn (int $offset) => Carbon::now()->subMonths($offset)->format('M Y'))
            ->push(Carbon::now()->format('M Y'));

        $monthlyRegistrations = $monthlyLabels->mapWithKeys(function (string $label) use ($beneficiaries): array {
            return [$label => $beneficiaries->filter(fn (Beneficiary $beneficiary) => $beneficiary->created_at?->format('M Y') === $label)->count()];
        });

        $genderDistribution = collect(Beneficiary::genders())
            ->mapWithKeys(fn (string $gender): array => [$gender => $beneficiaries->where('gender', $gender)->count()]);

        $ageGroupDistribution = collect(Beneficiary::ageGroups())
            ->mapWithKeys(function (string $label, string $key) use ($beneficiaries): array {
                $count = $beneficiaries->filter(function (Beneficiary $beneficiary) use ($key): bool {
                    $age = $beneficiary->age();

                    return match ($key) {
                        'minor' => $age !== null && $age <= 17,
                        'young_adult' => $age !== null && $age >= 18 && $age <= 29,
                        'adult' => $age !== null && $age >= 30 && $age <= 59,
                        'senior' => $age !== null && $age >= 60,
                        default => false,
                    };
                })->count();

                return [$label => $count];
            });

        $priorityCategories = collect(['Senior Citizen', 'PWD', 'Solo Parent'])
            ->mapWithKeys(fn (string $category): array => [$category => $beneficiaries->where('category', $category)->count()]);

        $statusDistribution = collect([
            'Active' => $summary['activeBeneficiaries'],
            'Pending Review' => $summary['pendingBeneficiaries'],
            'Inactive' => $summary['inactiveBeneficiaries'],
        ]);

        $mostAssistedBarangays = $assistanceLogs
            ->groupBy(fn (AssistanceLog $log) => $log->beneficiary?->barangay ?? 'Unassigned')
            ->map(fn ($logs, string $barangay): array => [
                'barangay' => $barangay,
                'total' => $logs->count(),
                'amount' => (float) $logs->sum('amount'),
            ])
            ->sortByDesc('total')
            ->take(5)
            ->values();

        $assistanceByBarangay = $assistanceLogs
            ->groupBy(fn (AssistanceLog $log) => $log->beneficiary?->barangay ?? 'Unassigned')
            ->map(fn ($logs) => (float) $logs->sum('amount'))
            ->sortDesc();

        $topBarangayDistribution = $this->collapseDistribution($perBarangay, 6);
        $topAssistanceDistribution = $this->collapseDistribution($assistanceByBarangay, 6);

        $coverageSnapshot = $perBarangay->map(function (int $count) use ($summary): float {
            if ($summary['totalBeneficiaries'] === 0) {
                return 0;
            }

            return round(($count / $summary['totalBeneficiaries']) * 100, 2);
        })->take(6);

        $charts = [
            'registration' => [
                'type' => 'line',
                'labels' => $monthlyRegistrations->keys(),
                'datasets' => [[
                    'label' => 'Registrations',
                    'data' => $monthlyRegistrations->values(),
                    'borderColor' => '#0f4c81',
                    'backgroundColor' => 'rgba(15, 76, 129, 0.12)',
                    'tension' => 0.35,
                    'fill' => true,
                ]],
                'options' => [
                    'plugins' => [
                        'legend' => ['display' => false],
                    ],
                ],
            ],
            'status' => [
                'type' => 'doughnut',
                'labels' => $statusDistribution->keys(),
                'datasets' => [[
                    'data' => $statusDistribution->values(),
                    'backgroundColor' => ['#2e7d32', '#f4a300', '#94a3b8'],
                    'borderWidth' => 0,
                ]],
                'options' => [
                    'cutout' => '68%',
                ],
            ],
            'barangay' => [
                'type' => 'pie',
                'labels' => $topBarangayDistribution->keys(),
                'datasets' => [[
                    'label' => 'Beneficiary Share',
                    'data' => $topBarangayDistribution->values(),
                    'backgroundColor' => ['#0f4c81', '#1c75bc', '#1fb5c7', '#51c1a5', '#89d6b9', '#cfeec6', '#e6f4ff'],
                    'borderWidth' => 0,
                ]],
            ],
            'gender' => [
                'type' => 'pie',
                'labels' => $genderDistribution->keys(),
                'datasets' => [[
                    'data' => $genderDistribution->values(),
                    'backgroundColor' => ['#0f4c81', '#1fb5c7', '#bddff9'],
                    'borderWidth' => 0,
                ]],
            ],
            'age' => [
                'type' => 'doughnut',
                'labels' => $ageGroupDistribution->keys(),
                'datasets' => [[
                    'data' => $ageGroupDistribution->values(),
                    'backgroundColor' => ['#0f4c81', '#1c75bc', '#1fb5c7', '#d3ecff'],
                    'borderWidth' => 0,
                ]],
                'options' => [
                    'cutout' => '62%',
                ],
            ],
            'priority' => [
                'type' => 'pie',
                'labels' => $priorityCategories->keys(),
                'datasets' => [[
                    'data' => $priorityCategories->values(),
                    'backgroundColor' => ['#f97316', '#0f4c81', '#1fb5c7'],
                    'borderWidth' => 0,
                ]],
            ],
            'assistance' => [
                'type' => 'bar',
                'labels' => $topAssistanceDistribution->keys(),
                'datasets' => [[
                    'label' => 'Aid Released (PHP)',
                    'data' => $topAssistanceDistribution->values(),
                    'backgroundColor' => '#0f4c81',
                    'borderRadius' => 10,
                ]],
                'options' => [
                    'indexAxis' => 'y',
                    'plugins' => [
                        'legend' => ['display' => false],
                    ],
                ],
            ],
        ];

        return view('dashboard', [
            'summary' => $summary,
            'recentBeneficiaries' => Beneficiary::query()->whereNull('archived_at')->latest()->take(6)->get(),
            'mostAssistedBarangays' => $mostAssistedBarangays,
            'coverageSnapshot' => $coverageSnapshot,
            'charts' => $charts,
        ]);
    }

    private function collapseDistribution(Collection $distribution, int $limit): Collection
    {
        if ($distribution->count() <= $limit) {
            return $distribution;
        }

        $topItems = $distribution->take($limit);
        $remainingTotal = $distribution->slice($limit)->sum();

        if ($remainingTotal > 0) {
            $topItems->put('Others', $remainingTotal);
        }

        return $topItems;
    }
}
