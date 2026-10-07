@extends('layouts.app')

@section('title', 'Event')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-semibold">Event</h1>

        @can('create', App\Models\Event::class)
            <a href="{{ route('events.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                + Buat Event
            </a>
        @endcan
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama event..."
               class="w-64 rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
        <select name="status" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
            <option value="">Semua status</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm hover:bg-slate-100">Filter</button>
        @if (request()->hasAny(['q', 'status']))
            <a href="{{ route('events.index') }}" class="px-2 py-2 text-sm text-slate-500 hover:text-slate-800">Reset</a>
        @endif
    </form>

    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Event</th>
                    <th class="px-4 py-3">Waktu</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Peserta</th>
                    <th class="px-4 py-3 text-right">Hadir</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($events as $event)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('events.show', $event) }}" class="font-medium text-indigo-600 hover:underline">{{ $event->name }}</a>
                            @if ($event->location)
                                <div class="text-xs text-slate-500">{{ $event->location }}</div>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">{{ $event->starts_at->translatedFormat('d M Y, H:i') }}</td>
                        <td class="px-4 py-3">@include('events._status', ['status' => $event->effectiveStatus()])</td>
                        <td class="px-4 py-3 text-right">{{ $event->participants_count }}</td>
                        <td class="px-4 py-3 text-right">{{ $event->attendances_count }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <a href="{{ route('events.show', $event) }}" class="text-slate-600 hover:text-slate-900">Detail</a>
                            @can('update', $event)
                                <a href="{{ route('events.edit', $event) }}" class="ml-3 text-slate-600 hover:text-slate-900">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-slate-500">Belum ada event.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $events->links() }}</div>
@endsection
