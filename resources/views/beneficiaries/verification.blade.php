<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $beneficiary->full_name }} Verification</title>
    @vite(['resources/css/app.css'])
</head>
<body class="verification-surface">
    <section class="verification-card">
        <div>
            <p class="eyebrow">Verification Result</p>
            <h1 class="page-title">Valid beneficiary profile</h1>
            <p class="section-copy">This QR code is registered under the Government Beneficiary Management System.</p>
        </div>

        <div class="verification-card__content">
            <div class="qr-panel">{!! $qrSvg !!}</div>
            <div class="detail-list">
                <div><dt>Beneficiary Number</dt><dd>{{ $beneficiary->beneficiary_number }}</dd></div>
                <div><dt>Full Name</dt><dd>{{ $beneficiary->full_name }}</dd></div>
                <div><dt>Barangay</dt><dd>{{ $beneficiary->barangay }}</dd></div>
                <div><dt>Status</dt><dd>{{ ucfirst($beneficiary->status) }}</dd></div>
                <div><dt>Category</dt><dd>{{ $beneficiary->category }}</dd></div>
                <div><dt>Date Issued</dt><dd>{{ optional($beneficiary->date_issued)->format('M d, Y') }}</dd></div>
            </div>
        </div>
    </section>
</body>
</html>
