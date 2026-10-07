{{-- Dekorasi latar halaman publik: bentuk geometris biru di pojok kanan atas dan kiri bawah. --}}
<svg class="pointer-events-none absolute right-0 top-0 -z-10 w-[45vw] max-w-[820px] md:w-[42vw]" viewBox="0 0 800 1000" fill="none" aria-hidden="true">
    <defs>
        <linearGradient id="enc-tr-dark" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="#4a6aa1"/>
            <stop offset="0.5" stop-color="#1b2f5c"/>
            <stop offset="1" stop-color="#0f1d42"/>
        </linearGradient>
        <linearGradient id="enc-tr-bright" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="#9ec3f0"/>
            <stop offset="1" stop-color="#4a8ae0"/>
        </linearGradient>
        <linearGradient id="enc-tr-soft" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="#c9dcf6" stop-opacity="0.75"/>
            <stop offset="1" stop-color="#c9dcf6" stop-opacity="0"/>
        </linearGradient>
    </defs>
    <rect x="470" y="260" width="520" height="520" rx="110" transform="rotate(-35 730 520)" fill="url(#enc-tr-soft)"/>
    <polygon points="0,0 800,0 800,470 700,375 650,215" fill="url(#enc-tr-dark)"/>
    <polygon points="700,375 800,470 800,560" fill="url(#enc-tr-bright)"/>
</svg>

<svg class="pointer-events-none absolute bottom-0 left-0 -z-10 hidden w-[28vw] max-w-[560px] md:block" viewBox="0 0 715 900" fill="none" aria-hidden="true">
    <defs>
        <linearGradient id="enc-bl-soft" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="#ffffff" stop-opacity="0.9"/>
            <stop offset="1" stop-color="#bcd3f3" stop-opacity="0.85"/>
        </linearGradient>
        <linearGradient id="enc-bl-bright" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="#9ec3f0"/>
            <stop offset="1" stop-color="#4a8ae0"/>
        </linearGradient>
        <linearGradient id="enc-bl-dark" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="#1b2f5c"/>
            <stop offset="1" stop-color="#3f5f94"/>
        </linearGradient>
    </defs>
    <path d="M0 10 L320 300 Q430 400 370 520 L200 700 L0 520 Z" fill="url(#enc-bl-soft)"/>
    <polygon points="0,297 145,617 0,457" fill="url(#enc-bl-bright)"/>
    <polygon points="0,457 190,717 0,900" fill="url(#enc-bl-dark)"/>
    <polygon points="190,717 715,900 0,900" fill="url(#enc-bl-dark)"/>
</svg>
