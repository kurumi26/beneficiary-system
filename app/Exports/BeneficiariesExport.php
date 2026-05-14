<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

class BeneficiariesExport implements FromCollection, WithCustomStartCell, WithDrawings, WithEvents, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(
        private readonly Collection $beneficiaries,
        private readonly bool $includeBranding = false,
    )
    {
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->beneficiaries->values();
    }

    public function startCell(): string
    {
        return $this->includeBranding ? 'A5' : 'A1';
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'Beneficiary Number',
            'Full Name',
            'Barangay',
            'Status',
            'Category',
            'Gender',
            'Birthdate',
            'Contact Number',
            'Date Issued',
        ];
    }

    /**
     * @param  mixed  $beneficiary
     * @return list<string>
     */
    public function map($beneficiary): array
    {
        return [
            $beneficiary->beneficiary_number,
            $beneficiary->full_name,
            $beneficiary->barangay,
            ucfirst($beneficiary->status),
            $beneficiary->category,
            $beneficiary->gender,
            optional($beneficiary->birthdate)->format('M d, Y') ?? '',
            $beneficiary->contact_number ?? '',
            optional($beneficiary->date_issued)->format('M d, Y') ?? '',
        ];
    }

    public function drawings(): array
    {
        if (! $this->includeBranding || ! function_exists('imagecreatetruecolor')) {
            return [];
        }

        $drawing = new MemoryDrawing();
        $drawing->setName('Pili Camarines Sur Capitol and ENOTPILI');
        $drawing->setDescription('Government export branding');
        $drawing->setImageResource($this->brandBannerImage());
        $drawing->setRenderingFunction(MemoryDrawing::RENDERING_PNG);
        $drawing->setMimeType(MemoryDrawing::MIMETYPE_PNG);
        $drawing->setHeight(52);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(8);
        $drawing->setOffsetY(6);

        return [$drawing];
    }

    public function registerEvents(): array
    {
        if (! $this->includeBranding) {
            return [];
        }

        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('D1:I1');
                $sheet->mergeCells('D2:I2');
                $sheet->setCellValue('D1', 'Government Beneficiary Records');
                $sheet->setCellValue('D2', 'Pili, Camarines Sur Capitol · Generated '.now()->format('M d, Y h:i A'));
                $sheet->getRowDimension(1)->setRowHeight(34);
                $sheet->getRowDimension(2)->setRowHeight(22);
                $sheet->getRowDimension(4)->setRowHeight(8);

                $sheet->getStyle('D1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => '0F172A'],
                    ],
                ]);

                $sheet->getStyle('D2')->applyFromArray([
                    'font' => [
                        'size' => 10,
                        'color' => ['rgb' => '64748B'],
                    ],
                ]);

                $sheet->getStyle('A5:I5')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => '0F4C81'],
                    ],
                ]);
            },
        ];
    }

    private function brandBannerImage()
    {
        $image = imagecreatetruecolor(320, 64);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        $transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);
        imagefill($image, 0, 0, $transparent);
        imagealphablending($image, true);

        $navy = imagecolorallocate($image, 16, 22, 168);
        $cyan = imagecolorallocate($image, 101, 208, 240);
        $white = imagecolorallocate($image, 255, 255, 255);
        $orange = imagecolorallocate($image, 245, 159, 36);
        $amber = imagecolorallocate($image, 194, 112, 12);

        imagefilledellipse($image, 30, 32, 50, 50, $white);
        imageellipse($image, 30, 32, 48, 48, $navy);
        imageellipse($image, 30, 32, 38, 38, $navy);
        imagefilledpolygon($image, [20, 38, 30, 21, 40, 38], 3, $navy);
        imagefilledellipse($image, 37, 26, 10, 10, $navy);
        imageline($image, 12, 42, 48, 42, $navy);
        imageline($image, 16, 46, 44, 46, $navy);

        imagestring($image, 5, 64, 18, 'EN', $navy);
        imagefilledpolygon($image, [111, 14, 100, 31, 111, 50, 122, 31], 4, $amber);
        imagefilledpolygon($image, [111, 14, 112, 50, 122, 31], 3, $orange);
        imagestring($image, 5, 126, 18, 'TPILI', $cyan);

        return $image;
    }
}
