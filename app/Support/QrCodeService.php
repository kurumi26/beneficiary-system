<?php

namespace App\Support;

use App\Models\Beneficiary;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;

class QrCodeService
{
    public function forBeneficiary(Beneficiary $beneficiary, int $size = 180): string
    {
        return $this->svg(route('beneficiaries.verification', $beneficiary->qr_token), $size);
    }

    public function svg(string $data, int $size = 180): string
    {
        return (new Builder(
            writer: new SvgWriter(),
            data: $data,
            size: $size,
            margin: 0,
            errorCorrectionLevel: ErrorCorrectionLevel::High,
        ))->build()->getString();
    }

    public function png(string $data, int $size = 180): string
    {
        return (new Builder(
            writer: new PngWriter(),
            data: $data,
            size: $size,
            margin: 0,
            errorCorrectionLevel: ErrorCorrectionLevel::High,
        ))->build()->getString();
    }

    public function pngDataUri(string $data, int $size = 180): string
    {
        return 'data:image/png;base64,'.base64_encode($this->png($data, $size));
    }
}
