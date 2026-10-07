<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full flex-col bg-slate-50 font-sans text-slate-800 antialiased selection:bg-enc-500 selection:text-white">
    <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/95 shadow-xs backdrop-blur-sm">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
            <div class="flex items-center gap-6 sm:gap-8">
                <a href="{{ route('home') }}" class="group flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-enc-900 text-sm font-black tracking-tight text-white shadow-sm ring-1 ring-enc-950/20 transition group-hover:bg-enc-800">
                        <span class="text-enc-400">E</span>NC
                    </span>
                    <div class="flex flex-col">
                        <span class="text-sm font-bold tracking-tight text-slate-900 group-hover:text-enc-800">
                            {{ config('app.name', 'Event Attendance') }}
                        </span>
                        <span class="text-[11px] font-medium tracking-wide text-slate-500">
                            Enterprise Attendance Portal
                        </span>
                    </div>
                </a>

                <nav class="hidden items-center gap-1 sm:flex">
                    <a href="{{ route('events.index') }}"
                       class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs font-semibold tracking-wide transition {{ request()->routeIs('events.*') ? 'bg-enc-50 text-enc-800 ring-1 ring-enc-200/80' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-enc-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        Daftar Event
                    </a>
                </nav>
            </div>

            <div class="flex items-center gap-3 sm:gap-4">
                <div class="flex items-center gap-2.5 rounded-lg border border-slate-200/60 bg-slate-50/60 px-3 py-1.5 text-xs">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-enc-800 text-[11px] font-bold text-white shadow-xs">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </span>
                    <div class="hidden flex-col sm:flex">
                        <span class="font-semibold text-slate-900 leading-tight">{{ auth()->user()->name }}</span>
                        <span class="text-[10px] text-slate-500">{{ auth()->user()->email }}</span>
                    </div>
                    <span class="rounded-md border px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider {{ auth()->user()->isAdmin() ? 'border-indigo-200 bg-indigo-50 text-indigo-700' : 'border-sky-200 bg-sky-50 text-sky-700' }}">
                        {{ auth()->user()->role->label() }}
                    </span>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            title="Keluar dari akun"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 shadow-xs transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        <span class="hidden sm:inline">Keluar</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-8 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50/90 p-4 text-sm text-emerald-900 shadow-xs">
                <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 flex-none text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <div class="flex-1 font-medium leading-snug">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50/90 p-4 text-sm text-rose-900 shadow-xs">
                <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 flex-none text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <div class="flex-1 font-medium leading-snug">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="mt-auto border-t border-slate-200/80 bg-white py-6 text-xs text-slate-500">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 px-4 sm:flex-row sm:px-6 lg:px-8">
            <div class="flex items-center gap-2">
                <span class="font-bold tracking-tight text-slate-700">ENC Corporation</span>
                <span class="text-slate-300">•</span>
                <span>Event Attendance Management System</span>
            </div>
            <div>
                © {{ now()->year }} Hak Cipta Dilindungi.
            </div>
        </div>
    </footer>
</body>
</html>
