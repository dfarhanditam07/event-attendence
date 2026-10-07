<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — {{ config('app.name', 'Event Attendance') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full items-center justify-center bg-slate-50 px-4 py-12 font-sans text-slate-800 antialiased selection:bg-enc-500 selection:text-white">
    <div class="w-full max-w-md">
        <!-- Brand Monogram -->
        <div class="mb-8 text-center">
            <div class="mx-auto inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-enc-900 text-lg font-black tracking-tight text-white shadow-md ring-1 ring-enc-950/20">
                <span class="text-enc-400">E</span>NC
            </div>
            <h1 class="mt-3 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                {{ config('app.name', 'Event Attendance') }}
            </h1>
            <p class="mt-1 text-xs font-medium tracking-wide text-slate-500 uppercase">
                Portal Manajemen Presensi Perusahaan
            </p>
        </div>

        <!-- Form Card -->
        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-xl shadow-slate-200/40">
            <div class="mb-6">
                <h2 class="text-base font-bold text-slate-900">Masuk ke Dashboard</h2>
                <p class="mt-0.5 text-xs text-slate-500">Gunakan email dan kata sandi panitia Anda untuk melanjutkan.</p>
            </div>

            <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Alamat Email
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </div>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                               placeholder="nama@perusahaan.com"
                               class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-9 pr-3 text-sm placeholder:text-slate-400 focus:border-enc-600 focus:outline-none focus:ring-2 focus:ring-enc-600/10">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                               placeholder="••••••••"
                               class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-9 pr-3 text-sm placeholder:text-slate-400 focus:border-enc-600 focus:outline-none focus:ring-2 focus:ring-enc-600/10">
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 text-xs font-medium text-slate-600 select-none cursor-pointer">
                        <input type="checkbox" name="remember" value="1"
                               class="h-4 w-4 rounded border-slate-300 text-enc-800 focus:ring-enc-600">
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-enc-800 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-enc-900 focus:outline-none focus:ring-2 focus:ring-enc-600/30">
                    <span>Masuk ke Dashboard</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"/>
                        <polyline points="12 5 19 12 12 19"/>
                    </svg>
                </button>
            </form>

            <div class="mt-6 border-t border-slate-100 pt-4 text-center">
                <p class="text-[11px] text-slate-400">
                    Lupa akses atau butuh pendaftaran akun? Hubungi Administrator TI perusahaan.
                </p>
            </div>
        </div>

        <!-- Security Notice -->
        <div class="mt-6 text-center text-xs text-slate-400">
            <div class="flex items-center justify-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                <span>Koneksi Aman Terenkripsi • ENC Corporation</span>
            </div>
            <p class="mt-1 text-[11px]">© {{ now()->year }} Hak Cipta Dilindungi.</p>
        </div>
    </div>
</body>
</html>
