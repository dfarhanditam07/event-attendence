<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Check-in') — {{ $event->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">
    <div class="relative isolate flex min-h-screen flex-col overflow-hidden bg-gradient-to-b from-white to-slate-100">
        @include('public._background')

        <header class="relative flex items-center justify-between gap-4 px-6 py-5 sm:px-12">
            <div class="flex items-center gap-3 sm:gap-4">
                <span class="text-4xl font-black leading-none tracking-tighter text-enc-800 sm:text-5xl">
                    <span class="text-enc-500">E</span>NC
                </span>
                <span class="h-10 w-px bg-slate-300" aria-hidden="true"></span>
                <span class="text-xs font-medium uppercase leading-tight tracking-wide text-enc-900 sm:text-sm">
                    Event Management<br>System
                </span>
            </div>

            <a href="{{ route('login') }}" aria-label="Login panitia"
               class="inline-flex h-11 w-11 flex-none items-center justify-center rounded-full bg-white/90 text-enc-700 shadow-md ring-1 ring-slate-200 transition hover:bg-white hover:text-enc-900 sm:h-12 sm:w-12">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="8" r="3.5"/>
                    <path d="M5 20c.8-3.6 3.7-5.5 7-5.5s6.2 1.9 7 5.5"/>
                </svg>
            </a>
        </header>

        <main class="relative mx-auto w-full max-w-5xl flex-1 px-4 pb-10 pt-6 sm:pt-12">
            <div class="text-center">
                <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-3">
                    <h1 class="text-3xl font-extrabold uppercase tracking-tight text-enc-950 sm:text-4xl lg:text-5xl">{{ $event->name }}</h1>
                    @include('events._status', ['status' => $event->effectiveStatus(), 'size' => 'lg'])
                </div>

                <p class="mt-4 flex flex-wrap items-center justify-center gap-x-8 gap-y-2 text-sm text-slate-700 sm:text-base">
                    <span class="inline-flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-none text-enc-900" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3.5" y="5" width="17" height="15.5" rx="2.5"/>
                            <path d="M3.5 10h17M8 3v4M16 3v4"/>
                        </svg>
                        {{ $event->starts_at->translatedFormat('l, d F Y') }} • {{ $event->starts_at->format('H:i') }} {{ $event->starts_at->format('T') }}
                    </span>
                    @if($event->location)
                        <span class="inline-flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-none text-enc-900" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 21s-6.5-5.6-6.5-11a6.5 6.5 0 1 1 13 0c0 5.4-6.5 11-6.5 11z"/>
                                <circle cx="12" cy="10" r="2.4"/>
                            </svg>
                            {{ $event->location }}
                        </span>
                    @endif
                </p>
            </div>

            <div class="mx-auto mt-8 w-full max-w-2xl sm:mt-10">
                @if($errors->any())
                    <div class="mb-5 rounded-md border border-red-200 bg-red-50 p-4 text-sm">
                        <ul class="list-inside list-disc space-y-0.5 text-red-700">
                            @foreach($errors->all() as $m)
                                <li>{{ $m }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')

                <footer class="mt-10 border-t border-slate-300/80 pt-8 text-center text-sm text-slate-700">
                    <p>Butuh bantuan? Hubungi Panitia Acara di meja registrasi.</p>
                    <p class="mt-8">© {{ now()->year }} ENC Corporation. All rights reserved.</p>
                </footer>
            </div>
        </main>
    </div>
</body>
</html>
