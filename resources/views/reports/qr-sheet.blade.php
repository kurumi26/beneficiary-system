<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Beneficiary QR Codes</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; }
        .report-header { margin-bottom: 22px; padding-bottom: 12px; border-bottom: 1px solid #cbd5e1; }
        .report-brand { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .report-brand td { border: 0; padding: 0; vertical-align: middle; }
        .report-brand__logos { width: 220px; }
        .report-brand__seal { width: 44px; height: 44px; vertical-align: middle; }
        .report-brand__wordmark { width: 148px; height: auto; margin-left: 8px; vertical-align: middle; }
        .cell { display: inline-block; width: 30%; border: 1px solid #cbd5e1; border-radius: 12px; padding: 14px; margin: 0 1.5% 16px 0; vertical-align: top; }
        .qr-code { margin-bottom: 10px; }
        .qr-code svg { width: 118px; height: 118px; display: block; }
        h1 { margin-bottom: 18px; }
        p { margin: 4px 0; }
    </style>
</head>
<body>
    <div class="report-header">
        <table class="report-brand">
            <tr>
                <td class="report-brand__logos">
                    <x-application-logo class="report-brand__seal" />
                    <x-system-wordmark class="report-brand__wordmark" />
                </td>
                <td>
                    <h1>Beneficiary QR Code Sheet</h1>
                    <p>Generated on {{ now()->format('M d, Y h:i A') }}</p>
                </td>
            </tr>
        </table>
    </div>
    @foreach ($cards as $card)
        <div class="cell">
            <div class="qr-code">{!! $card['qrSvg'] !!}</div>
            <p><strong>{{ $card['beneficiary']->full_name }}</strong></p>
            <p>{{ $card['beneficiary']->beneficiary_number }}</p>
            <p>{{ $card['beneficiary']->barangay }}</p>
        </div>
    @endforeach
</body>
</html>
