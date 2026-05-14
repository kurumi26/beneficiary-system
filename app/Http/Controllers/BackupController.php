<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\AssistanceLog;
use App\Models\Beneficiary;
use App\Models\BeneficiaryDocument;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BackupController extends Controller
{
    public function index(): View
    {
        $files = collect(Storage::disk('local')->files('backups'))
            ->map(fn (string $path): array => [
                'name' => basename($path),
                'path' => $path,
                'size' => Storage::disk('local')->size($path),
                'last_modified' => Storage::disk('local')->lastModified($path),
            ])
            ->sortByDesc('last_modified')
            ->values();

        return view('backups.index', ['files' => $files]);
    }

    public function store(ActivityLogger $activityLogger)
    {
        $payload = [
            'generated_at' => now()->toIso8601String(),
            'beneficiaries' => Beneficiary::query()->with(['assistanceLogs', 'documents'])->get()->map(function (Beneficiary $beneficiary): array {
                return [
                    ...$beneficiary->toArray(),
                    'assistance_logs' => $beneficiary->assistanceLogs->toArray(),
                    'documents' => $beneficiary->documents->toArray(),
                ];
            })->all(),
            'activity_logs' => ActivityLog::query()->latest()->take(500)->get()->toArray(),
        ];

        $file = 'backups/gbms-backup-'.now()->format('Ymd-His').'.json';
        Storage::disk('local')->put($file, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $activityLogger->log('backup.created', 'Created a data backup.', null, [
            'file' => $file,
            'beneficiary_count' => count($payload['beneficiaries']),
        ]);

        return response()->download(Storage::disk('local')->path($file));
    }

    public function restore(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $validated = $request->validate([
            'backup_file' => ['required', 'file', 'mimetypes:application/json,text/plain', 'max:10240'],
        ]);

        $contents = file_get_contents($validated['backup_file']->getRealPath());
        $payload = json_decode($contents ?: '{}', true, 512, JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($payload): void {
            BeneficiaryDocument::query()->delete();
            AssistanceLog::query()->delete();
            ActivityLog::query()->delete();
            Beneficiary::query()->delete();

            $beneficiaryMap = [];

            foreach ($payload['beneficiaries'] ?? [] as $record) {
                $assistanceLogs = $record['assistance_logs'] ?? [];
                $documents = $record['documents'] ?? [];

                $oldNumber = $record['beneficiary_number'] ?? null;

                unset($record['id'], $record['assistance_logs'], $record['documents']);

                $newBeneficiaryId = DB::table('beneficiaries')->insertGetId($record);

                if ($oldNumber) {
                    $beneficiaryMap[$oldNumber] = $newBeneficiaryId;
                }

                foreach ($assistanceLogs as $log) {
                    unset($log['id']);
                    $log['beneficiary_id'] = $newBeneficiaryId;
                    DB::table('assistance_logs')->insert($log);
                }

                foreach ($documents as $document) {
                    unset($document['id']);
                    $document['beneficiary_id'] = $newBeneficiaryId;
                    DB::table('beneficiary_documents')->insert($document);
                }
            }

            foreach ($payload['activity_logs'] ?? [] as $log) {
                unset($log['id']);

                if (($log['subject_type'] ?? null) === Beneficiary::class && isset($log['properties']['beneficiary_number'])) {
                    $log['subject_id'] = $beneficiaryMap[$log['properties']['beneficiary_number']] ?? null;
                }

                if (is_array($log['properties'] ?? null)) {
                    $log['properties'] = json_encode($log['properties']);
                }

                DB::table('activity_logs')->insert($log);
            }
        });

        $activityLogger->log('backup.restored', 'Restored data from uploaded backup.');

        return redirect()->route('backups.index')->with('status', 'Backup restored successfully.');
    }
}
