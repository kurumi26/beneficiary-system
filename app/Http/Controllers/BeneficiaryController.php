<?php

namespace App\Http\Controllers;

use App\Exports\BeneficiariesExport;
use App\Http\Requests\StoreBeneficiaryRequest;
use App\Http\Requests\UpdateBeneficiaryRequest;
use App\Models\Beneficiary;
use App\Support\ActivityLogger;
use App\Support\QrCodeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;

class BeneficiaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        return view('beneficiaries.index', [
            'beneficiaries' => $this->queryBeneficiaries($request)->withCount('assistanceLogs')->paginate(12)->withQueryString(),
            'filters' => $request->all(),
            'barangays' => Beneficiary::query()->select('barangay')->distinct()->orderBy('barangay')->pluck('barangay'),
            'statuses' => Beneficiary::statuses(),
            'genders' => Beneficiary::genders(),
            'categories' => Beneficiary::categories(),
            'ageGroups' => Beneficiary::ageGroups(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('beneficiaries.create', $this->formData(new Beneficiary()));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBeneficiaryRequest $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $beneficiary = Beneficiary::query()->create($this->validatedBeneficiaryData($request));

        $this->storeSupportingDocuments($request, $beneficiary);

        $activityLogger->log(
            'beneficiary.created',
            "Registered beneficiary {$beneficiary->full_name}.",
            $beneficiary,
            ['beneficiary_number' => $beneficiary->beneficiary_number],
        );

        return redirect()
            ->route('beneficiaries.show', $beneficiary)
            ->with('status', 'Beneficiary registered successfully. QR code and ID card are ready for printing.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Beneficiary $beneficiary, QrCodeService $qrCodeService): View
    {
        $beneficiary->load([
            'creator',
            'assistanceLogs.creator',
            'documents.uploader',
            'activityLogs.user',
        ]);

        return view('beneficiaries.show', [
            'beneficiary' => $beneficiary,
            'qrSvg' => $qrCodeService->forBeneficiary($beneficiary),
            'assistanceTotal' => (float) $beneficiary->assistanceLogs->sum('amount'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Beneficiary $beneficiary): View
    {
        return view('beneficiaries.edit', $this->formData($beneficiary));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBeneficiaryRequest $request, Beneficiary $beneficiary, ActivityLogger $activityLogger): RedirectResponse
    {
        $beneficiary->update($this->validatedBeneficiaryData($request, $beneficiary));

        $this->storeSupportingDocuments($request, $beneficiary);

        $activityLogger->log(
            'beneficiary.updated',
            "Updated beneficiary {$beneficiary->full_name}.",
            $beneficiary,
            ['beneficiary_number' => $beneficiary->beneficiary_number, 'status' => $beneficiary->status],
        );

        return redirect()
            ->route('beneficiaries.show', $beneficiary)
            ->with('status', 'Beneficiary record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Beneficiary $beneficiary, ActivityLogger $activityLogger): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $beneficiary->load('documents');

        $fullName = $beneficiary->full_name;
        $number = $beneficiary->beneficiary_number;

        foreach ([$beneficiary->valid_id_path, $beneficiary->photo_path] as $path) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
        }

        foreach ($beneficiary->documents as $document) {
            Storage::disk('public')->delete($document->file_path);
        }

        $beneficiary->delete();

        $activityLogger->log('beneficiary.deleted', "Deleted beneficiary {$fullName}.", null, [
            'beneficiary_number' => $number,
        ]);

        return redirect()->route('beneficiaries.index')->with('status', 'Beneficiary record deleted successfully.');
    }

    public function archive(Beneficiary $beneficiary, ActivityLogger $activityLogger): RedirectResponse
    {
        $beneficiary->update([
            'status' => Beneficiary::STATUS_INACTIVE,
            'archived_at' => now(),
        ]);

        $activityLogger->log('beneficiary.archived', "Archived beneficiary {$beneficiary->full_name}.", $beneficiary, [
            'beneficiary_number' => $beneficiary->beneficiary_number,
        ]);

        return redirect()->route('beneficiaries.show', $beneficiary)->with('status', 'Beneficiary archived successfully.');
    }

    public function export(Request $request, string $format)
    {
        return $this->exportBeneficiaries($this->collectBeneficiaries($request), $format);
    }

    public function bulkAction(Request $request, QrCodeService $qrCodeService, ActivityLogger $activityLogger)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:deactivate,export_csv,export_excel,export_pdf,print_id_cards,export_qr'],
            'selected' => ['required', 'array', 'min:1'],
            'selected.*' => ['integer', 'exists:beneficiaries,id'],
        ]);

        $beneficiaries = Beneficiary::query()->whereIn('id', $validated['selected'])->get();

        if ($validated['action'] === 'deactivate') {
            Beneficiary::query()->whereIn('id', $validated['selected'])->update([
                'status' => Beneficiary::STATUS_INACTIVE,
            ]);

            $activityLogger->log('beneficiary.bulk_deactivated', 'Bulk deactivated beneficiaries.', null, [
                'count' => $beneficiaries->count(),
            ]);

            return redirect()->route('beneficiaries.index')->with('status', 'Selected beneficiaries were marked inactive.');
        }

        if ($validated['action'] === 'print_id_cards') {
            return view('beneficiaries.batch-id-cards', [
                'cards' => $this->buildCards($beneficiaries, $qrCodeService),
                'autoPrint' => true,
            ]);
        }

        if ($validated['action'] === 'export_qr') {
            return Pdf::loadView('reports.qr-sheet', [
                'cards' => $this->buildCards($beneficiaries, $qrCodeService, 120),
            ])->download('beneficiary-qr-codes.pdf');
        }

        return $this->exportBeneficiaries($beneficiaries, str_replace('export_', '', $validated['action']));
    }

    public function idCard(Beneficiary $beneficiary, QrCodeService $qrCodeService): View
    {
        return view('beneficiaries.id-card', [
            'card' => $this->buildCards(collect([$beneficiary]), $qrCodeService)->first(),
            'autoPrint' => false,
        ]);
    }

    public function idCardPdf(Beneficiary $beneficiary, QrCodeService $qrCodeService)
    {
        $card = $this->buildCards(collect([$beneficiary]), $qrCodeService)->first();

        return Pdf::loadView('beneficiaries.id-card-pdf', [
            'card' => $card,
            'brandBannerSrc' => $this->idCardBrandBannerDataUri(),
            'photoSrc' => $this->publicImageDataUri($beneficiary->photo_path),
            'qrImageSrc' => $qrCodeService->pngDataUri(route('beneficiaries.verification', $beneficiary->qr_token), 220),
        ])->setPaper([0, 0, 556, 289])->download("{$beneficiary->beneficiary_number}-id-card.pdf");
    }

    public function verification(string $token, QrCodeService $qrCodeService): View
    {
        $beneficiary = Beneficiary::query()->where('qr_token', $token)->firstOrFail();

        return view('beneficiaries.verification', [
            'beneficiary' => $beneficiary,
            'qrSvg' => $qrCodeService->forBeneficiary($beneficiary, 120),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Beneficiary $beneficiary): array
    {
        return [
            'beneficiary' => $beneficiary,
            'statuses' => Beneficiary::statuses(),
            'genders' => Beneficiary::genders(),
            'categories' => Beneficiary::categories(),
            'civilStatuses' => Beneficiary::civilStatuses(),
        ];
    }

    private function queryBeneficiaries(Request $request): Builder
    {
        return Beneficiary::query()->filter($request->only([
            'archived',
            'search',
            'barangay',
            'status',
            'gender',
            'category',
            'registered_from',
            'registered_to',
            'age_group',
            'sort',
        ]));
    }

    private function collectBeneficiaries(Request $request): EloquentCollection
    {
        $selected = collect($request->input('selected', []))->filter();

        if ($selected->isNotEmpty()) {
            return Beneficiary::query()->whereIn('id', $selected)->get();
        }

        return $this->queryBeneficiaries($request)->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedBeneficiaryData(Request $request, ?Beneficiary $beneficiary = null): array
    {
        $data = $request->safe()->except(['valid_id', 'photo', 'supporting_documents', 'first_name', 'middle_name', 'last_name']);
        $data['created_by'] = $beneficiary?->created_by ?? $request->user()->id;

        if ($request->hasFile('valid_id')) {
            if ($beneficiary?->valid_id_path) {
                Storage::disk('public')->delete($beneficiary->valid_id_path);
            }

            $data['valid_id_path'] = $request->file('valid_id')->store('beneficiaries/valid-ids', 'public');
        }

        if ($request->hasFile('photo')) {
            if ($beneficiary?->photo_path) {
                Storage::disk('public')->delete($beneficiary->photo_path);
            }

            $data['photo_path'] = $request->file('photo')->store('beneficiaries/photos', 'public');
        }

        return $data;
    }

    private function storeSupportingDocuments(Request $request, Beneficiary $beneficiary): void
    {
        foreach ($request->file('supporting_documents', []) as $file) {
            $beneficiary->documents()->create([
                'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'file_path' => $file->store('beneficiaries/documents', 'public'),
                'uploaded_by' => $request->user()->id,
            ]);
        }
    }

    private function exportBeneficiaries(EloquentCollection $beneficiaries, string $format)
    {
        $filePrefix = 'beneficiaries-'.now()->format('Ymd-His');

        return match ($format) {
            'csv' => Excel::download(new BeneficiariesExport($beneficiaries), "{$filePrefix}.csv", ExcelWriter::CSV),
            'excel', 'xlsx' => Excel::download(new BeneficiariesExport($beneficiaries, true), "{$filePrefix}.xlsx", ExcelWriter::XLSX),
            'pdf' => Pdf::loadView('reports.beneficiaries-pdf', ['beneficiaries' => $beneficiaries])->download("{$filePrefix}.pdf"),
            default => abort(404),
        };
    }

    private function buildCards(Collection $beneficiaries, QrCodeService $qrCodeService, int $size = 140): Collection
    {
        return $beneficiaries->map(function (Beneficiary $beneficiary) use ($qrCodeService, $size): array {
            return [
                'beneficiary' => $beneficiary,
                'qrSvg' => $qrCodeService->forBeneficiary($beneficiary, $size),
            ];
        });
    }

    private function publicImageDataUri(?string $path): ?string
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $mimeType = Storage::disk('public')->mimeType($path) ?: 'application/octet-stream';

        return 'data:'.$mimeType.';base64,'.base64_encode(Storage::disk('public')->get($path));
    }

    private function idCardBrandBannerDataUri(): ?string
    {
        if (! function_exists('imagecreatetruecolor') || ! function_exists('imagepng')) {
            return null;
        }

        $image = imagecreatetruecolor(420, 92);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        $transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);
        imagefill($image, 0, 0, $transparent);
        imagealphablending($image, true);

        if (function_exists('imageantialias')) {
            imageantialias($image, true);
        }

        $navy = imagecolorallocate($image, 16, 22, 168);
        $cyan = imagecolorallocate($image, 101, 208, 240);
        $white = imagecolorallocate($image, 255, 255, 255);
        $orange = imagecolorallocate($image, 245, 159, 36);
        $amber = imagecolorallocate($image, 194, 112, 12);

        imagefilledellipse($image, 40, 46, 62, 62, $white);
        imageellipse($image, 40, 46, 58, 58, $navy);
        imageellipse($image, 40, 46, 46, 46, $navy);
        imagefilledpolygon($image, [28, 54, 40, 30, 52, 54], 3, $navy);
        imagefilledellipse($image, 48, 38, 11, 11, $navy);
        imageline($image, 19, 58, 61, 58, $navy);
        imageline($image, 24, 63, 56, 63, $navy);
        imageline($image, 29, 68, 51, 68, $navy);

        imagestring($image, 5, 90, 29, 'EN', $navy);
        imagefilledpolygon($image, [157, 19, 143, 45, 157, 73, 171, 45], 4, $amber);
        imagefilledpolygon($image, [157, 19, 159, 73, 171, 45], 3, $orange);
        imagestring($image, 5, 178, 29, 'TPILI', $cyan);

        ob_start();
        imagepng($image);
        $png = ob_get_clean();
        imagedestroy($image);

        if (! is_string($png) || $png === '') {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode($png);
    }
}
