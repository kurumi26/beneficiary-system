@php
    $wordmarkSuffix = str_replace('.', '', uniqid('enotpili', true));
    $blueGradientId = 'enotpili-blue-'.$wordmarkSuffix;
    $orangeGradientId = 'enotpili-orange-'.$wordmarkSuffix;
@endphp

<svg viewBox="0 0 360 96" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="ENOTPILI wordmark" {{ $attributes }}>
    <defs>
        <linearGradient id="{{ $blueGradientId }}" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#0918a8" />
            <stop offset="100%" stop-color="#65d0f0" />
        </linearGradient>
        <linearGradient id="{{ $orangeGradientId }}" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#f59f24" />
            <stop offset="100%" stop-color="#c2700c" />
        </linearGradient>
    </defs>

    <g fill="url(#{{ $blueGradientId }})" stroke="#112a99" stroke-width="3.4" paint-order="stroke fill" font-family="Arial Black, Arial, sans-serif" font-style="italic" font-weight="900">
        <text x="4" y="60" font-size="56" letter-spacing="1.5">EN</text>
        <text x="128" y="60" font-size="56" letter-spacing="1.5">T</text>
        <text x="169" y="76" font-size="76" letter-spacing="1.5">PILI</text>
    </g>

    <g transform="translate(92 9)">
        <path d="M23 4C9 18 4 34 4 49c0 15 6 27 19 34C12 64 14 29 23 4Z" fill="#8a570d" />
        <path d="M23 4c15 14 22 29 22 45 0 15-7 27-22 34 9-19 8-54 0-79Z" fill="url(#{{ $orangeGradientId }})" />
        <path d="M23 9c-5 17-5 42 0 67" fill="none" stroke="#f5c16d" stroke-linecap="round" stroke-width="2.2" />
        <path d="M23 19c6 8 10 21 11 35" fill="none" stroke="#f7c978" stroke-linecap="round" stroke-width="1.6" />
        <path d="M23 28c-5 8-8 20-9 32" fill="none" stroke="#d9911e" stroke-linecap="round" stroke-width="1.4" />
    </g>
</svg>
