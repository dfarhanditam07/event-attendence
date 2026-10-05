<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
            <div class="flex items-center gap-6">
                <a href="{{ route('home') }}" class="text-lg font-semibold text-indigo-600">{{ config('app.name') }}</a>
                <nav class="flex gap-4 text-sm">
                    <a href="{{ route('events.index') }}"
                       class="{{ request()->routeIs('events.*') ? 'font-semibold text-slate-900' : 'text-slate-600 hover:text-slate-900' }}">
                        Event
                    </a>
                </nav>
            </div>

            <div class="flex items-center gap-3 text-sm">
                <span class="text-slate-600">
                    {{ auth()->user()->name }}
                    <span class="ml-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">
                        {{ auth()->user()->role->label() }}
                    </span>
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-md border border-slate-300 px-3 py-1.5 text-slate-700 hover:bg-slate-100">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8">
        @if (session('success'))
            <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
