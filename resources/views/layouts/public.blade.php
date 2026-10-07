<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Presensi Kehadiran') — {{ $event->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col bg-slate-50 font-sans text-slate-800 antialiased selection:bg-enc-500 selection:text-white">
    <div class="relative isolate flex min-h-full flex-1 flex-col overflow-hidden bg-gradient-to-b from-white via-slate-50/60 to-slate-100">
        @include('public._background')

        <!-- Public Top Navigation -->
        <header class="relative flex items-center justify-between gap-4 px-6 py-5 sm:px-12">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="flex items-center gap-2">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-enc-900 text-base font-black tracking-tight text-white shadow-md ring-1 ring-enc-950/20">
                        <span class="text-enc-400">E</span>NC
                    </span>
                    <span class="h-8 w-px bg-slate-200" aria-hidden="true"></span>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold uppercase tracking-wider text-enc-950 sm:text-sm">
                            Event Management
                        </span>
                        <span class="text-[10px] font-medium tracking-wide text-slate-500 uppercase">
                            Presensi Mandiri Peserta
                        </span>
                    </div>
                </div>
            </div>

            <a href="{{ route('login') }}"
               title="Portal Login Panitia"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200/90 bg-white/90 px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-xs backdrop-blur-sm transition hover:border-enc-300 hover:bg-white hover:text-enc-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-enc-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <span class="hidden sm:inline">Masuk Panitia</span>
            </a>
        </header>

        <!-- Main Content Area -->
        <main class="relative mx-auto w-full max-w-5xl flex-1 px-4 pb-12 pt-4 sm:pt-8">
            <div class="text-center">
                <div class="inline-flex flex-wrap items-center justify-center gap-3">
                    <h1 class="text-2xl font-black uppercase tracking-tight text-enc-950 sm:text-3xl lg:text-4xl">
                        {{ $event->name }}
                    </h1>
                </div>

                <div class="mt-2.5 flex justify-center">
                    @include('events._status', ['status' => $event->effectiveStatus(), 'size' => 'lg'])
                </div>

                <!-- Event Details Badge -->
                <div class="mt-4 flex flex-wrap items-center justify-center gap-2 text-xs sm:text-sm text-slate-600">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white/90 px-3.5 py-1.5 shadow-2xs backdrop-blur-xs font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-enc-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        {{ $event->starts_at->translatedFormat('l, d F Y') }} • {{ $event->starts_at->format('H:i') }} WIB
                    </span>

                    @if($event->location)
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white/90 px-3.5 py-1.5 shadow-2xs backdrop-blur-xs font-medium">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-enc-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            {{ $event->location }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- Content Container -->
            <div class="mx-auto mt-8 w-full max-w-2xl sm:mt-10">
                @if($errors->any())
                    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50/95 p-4 text-sm shadow-xs">
                        <div class="flex items-start gap-3">
                            <span class="flex h-5 w-5 flex-none items-center justify-center rounded-full bg-rose-600 text-xs font-bold text-white">!</span>
                            <ul class="list-inside list-disc space-y-1 text-rose-800 text-xs font-medium">
                                @foreach($errors->all() as $m)
                                    <li>{{ $m }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @yield('content')

                <!-- Public Footer -->
                <footer class="mt-12 border-t border-slate-200/80 pt-6 text-center text-xs text-slate-500">
                    <p class="font-medium text-slate-600">
                        Memerlukan bantuan kehadiran? Silakan hubungi Panitia Acara di meja registrasi.
                    </p>
                    <p class="mt-2 text-[11px] text-slate-400">
                        © {{ now()->year }} ENC Corporation • Sistem Presensi Kehadiran Perusahaan. All rights reserved.
                    </p>
                </footer>
            </div>
        </main>
    </div>
</body>
</html>
