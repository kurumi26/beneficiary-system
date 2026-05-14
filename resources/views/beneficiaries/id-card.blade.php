<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $card['beneficiary']->beneficiary_number }} ID Card</title>
    @vite(['resources/css/app.css'])
</head>
<body class="print-surface {{ $autoPrint ? 'print-immediately' : '' }}">
    @unless ($autoPrint)
        <section class="print-toolbar print-hide" aria-label="Print actions">
            <div>
                <p class="print-toolbar__eyebrow">ID Card Preview</p>
                <h1>Ready for hard copy printing</h1>
                <p>Use the print button below to open your browser print dialog.</p>
            </div>

            <div class="print-toolbar__actions">
                <button class="button-primary" type="button" onclick="window.print()">Print Hard Copy</button>
                <a class="button-secondary" href="{{ route('beneficiaries.id-card.pdf', $card['beneficiary']) }}">Download PDF</a>
                <a class="button-secondary" href="{{ route('beneficiaries.show', $card['beneficiary']) }}">Back to Profile</a>
            </div>
        </section>
    @endunless

    <article class="id-card-sheet">
        <div class="id-card">
            <div class="id-card__header">
                <div>
                    <p class="id-card__eyebrow">Local Government Unit</p>
                    <h1>Beneficiary Identification Card</h1>
                </div>
                <div class="id-card__brand">
                    <x-application-logo class="id-card__brand-seal" />
                    <x-system-wordmark class="id-card__brand-wordmark" />
                </div>
            </div>

            <div class="id-card__body">
                <div class="id-card__photo">
                    @if ($card['beneficiary']->photo_path)
                        <img src="{{ Storage::url($card['beneficiary']->photo_path) }}" alt="{{ $card['beneficiary']->full_name }}">
                    @else
                        <span>{{ strtoupper(substr($card['beneficiary']->full_name, 0, 1)) }}</span>
                    @endif
                </div>

                <div class="id-card__content">
                    <div>
                        <p class="id-card__label">Beneficiary Name</p>
                        <h2>{{ $card['beneficiary']->full_name }}</h2>
                    </div>
                    <p><strong>ID Number:</strong> {{ $card['beneficiary']->beneficiary_number }}</p>
                    <p><strong>Barangay:</strong> {{ $card['beneficiary']->barangay }}</p>
                    <p><strong>Date Issued:</strong> {{ optional($card['beneficiary']->date_issued)->format('M d, Y') }}</p>
                    <div class="id-card__signature">
                        <span>Authorized Signature</span>
                    </div>
                </div>

                <div class="id-card__qr">{!! $card['qrSvg'] !!}</div>
            </div>
        </div>
    </article>

    @if ($autoPrint)
        <script>window.print()</script>
    @endif
</body>
</html>
