<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $card['beneficiary']->beneficiary_number }} ID Card</title>
    <style>
        @page {
            margin: 0;
            size: 196mm 102mm;
        }

        body {
            margin: 0;
            padding: 7mm 9mm;
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            background: #ffffff;
        }

        .sheet {
            page-break-inside: avoid;
        }

        .id-card {
            width: 100%;
            border: 1px solid #cfe5f5;
            border-radius: 26px;
            padding: 18px 20px 16px;
            background: #eef8ff;
            page-break-inside: avoid;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            page-break-inside: avoid;
        }

        td {
            vertical-align: top;
        }

        .header td {
            border-bottom: 1px solid #d9e8f3;
            padding-bottom: 12px;
        }

        .eyebrow,
        .label,
        .signature {
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.24em;
            font-size: 11px;
            font-weight: 700;
            color: #5d7694;
        }

        h1 {
            margin: 6px 0 0;
            font-size: 20px;
            line-height: 1.15;
            font-weight: 700;
            color: #0f172a;
        }

        .brand-cell {
            width: 220px;
            text-align: right;
        }

        .brand-banner {
            width: 210px;
            height: auto;
            display: block;
            margin-left: auto;
        }

        .body-wrap {
            padding-top: 18px;
        }

        .photo-cell {
            width: 108px;
        }

        .content-cell {
            padding: 0 18px;
        }

        .qr-cell {
            width: 138px;
        }

        .photo-frame {
            width: 96px;
            height: 96px;
            border: 1px solid #d9e8f3;
            border-radius: 24px;
            background: #ffffff;
            overflow: hidden;
            text-align: center;
        }

        .photo-frame img {
            width: 96px;
            height: 96px;
            object-fit: cover;
        }

        .photo-fallback {
            font-size: 30px;
            line-height: 96px;
            font-weight: 700;
            color: #0369a1;
        }

        h2 {
            margin: 8px 0 12px;
            font-size: 18px;
            line-height: 1.2;
            font-weight: 700;
            color: #0f172a;
        }

        .detail {
            margin: 0 0 10px;
            font-size: 12px;
            line-height: 1.35;
            color: #334155;
        }

        .detail strong {
            color: #1e293b;
        }

        .signature {
            margin-top: 16px;
            padding-top: 12px;
            border-top: 1px solid #d9e8f3;
        }

        .qr-frame {
            width: 128px;
            height: 128px;
            margin-left: auto;
            border: 1px solid #d9e8f3;
            background: #ffffff;
            padding: 0;
        }

        .qr-frame img {
            width: 128px;
            height: 128px;
            display: block;
        }
    </style>
</head>
<body>
    @php
        $beneficiary = $card['beneficiary'];
    @endphp

    <div class="sheet">
        <div class="id-card">
            <table class="header">
                <tr>
                    <td>
                        <p class="eyebrow">Local Government Unit</p>
                        <h1>Beneficiary Identification Card</h1>
                    </td>
                    <td class="brand-cell">
                        @if ($brandBannerSrc)
                            <img class="brand-banner" src="{{ $brandBannerSrc }}" alt="Pili Capitol and ENOTPILI">
                        @endif
                    </td>
                </tr>
            </table>

            <div class="body-wrap">
                <table class="body">
                    <tr>
                        <td class="photo-cell">
                            <div class="photo-frame">
                                @if ($photoSrc)
                                    <img src="{{ $photoSrc }}" alt="{{ $beneficiary->full_name }}">
                                @else
                                    <div class="photo-fallback">{{ strtoupper(substr($beneficiary->full_name, 0, 1)) }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="content-cell">
                            <p class="label">Beneficiary Name</p>
                            <h2>{{ $beneficiary->full_name }}</h2>
                            <p class="detail"><strong>ID Number:</strong> {{ $beneficiary->beneficiary_number }}</p>
                            <p class="detail"><strong>Barangay:</strong> {{ $beneficiary->barangay }}</p>
                            <p class="detail"><strong>Date Issued:</strong> {{ optional($beneficiary->date_issued)->format('M d, Y') }}</p>
                            <p class="signature">Authorized Signature</p>
                        </td>
                        <td class="qr-cell">
                            <div class="qr-frame">
                                <img src="{{ $qrImageSrc }}" alt="{{ $beneficiary->beneficiary_number }} QR code">
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
