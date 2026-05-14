<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Beneficiary ID Cards</title>
    @vite(['resources/css/app.css'])
</head>
<body class="print-surface {{ $autoPrint ? 'print-immediately' : '' }}">
    <section class="id-card-grid">
        @foreach ($cards as $card)
            <article class="id-card compact">
                <div class="id-card__header">
                    <div>
                        <p class="id-card__eyebrow">LGU Beneficiary Program</p>
                        <h1>{{ $card['beneficiary']->beneficiary_number }}</h1>
                    </div>
                    <div class="id-card__brand id-card__brand--compact">
                        <x-application-logo class="id-card__brand-seal id-card__brand-seal--compact" />
                        <x-system-wordmark class="id-card__brand-wordmark id-card__brand-wordmark--compact" />
                    </div>
                </div>

                <div class="id-card__body">
                    <div class="id-card__photo small">
                        @if ($card['beneficiary']->photo_path)
                            <img src="{{ Storage::url($card['beneficiary']->photo_path) }}" alt="{{ $card['beneficiary']->full_name }}">
                        @else
                            <span>{{ strtoupper(substr($card['beneficiary']->full_name, 0, 1)) }}</span>
                        @endif
                    </div>
                    <div class="id-card__content compact">
                        <h2>{{ $card['beneficiary']->full_name }}</h2>
                        <p>{{ $card['beneficiary']->barangay }}</p>
                        <p>{{ optional($card['beneficiary']->date_issued)->format('M d, Y') }}</p>
                    </div>
                    <div class="id-card__qr small">{!! $card['qrSvg'] !!}</div>
                </div>
            </article>
        @endforeach
    </section>

    @if ($autoPrint)
        <script>window.print()</script>
    @endif
</body>
</html>
