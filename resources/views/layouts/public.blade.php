<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Check-in') — {{ $event->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-b from-indigo-50 via-white to-slate-50 font-sans text-slate-800 antialiased">
    <main class="mx-auto max-w-2xl px-4 py-10">
        <div class="mb-6 text-center">
            <div class="mx-auto mb-4 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-lg shadow-indigo-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 6 9 17l-5-5"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $event->name }}</h1>
            <p class="mt-2 text-sm text-slate-600">
                {{ $event->starts_at->translatedFormat('l, d F Y, H:i') }}
                @if($event->location) · {{ $event->location }} @endif
            </p>
            @include('events._status', ['status' => $event->status])
        </div>

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

        <div class="mt-8 border-t border-slate-200 pt-6 text-center text-xs text-slate-500">
            <p>Butuh bantuan? Hubungi panitia acara.</p>
        </div>
    </main>
</body>
</html>
