@php
    $input = 'w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500';
    $currentStatus = old('status', $event->status?->value);
@endphp

@csrf

<div class="space-y-5">
    <div>
        <label for="name" class="mb-1 block text-sm font-medium">Nama event <span class="text-red-500">*</span></label>
        <input id="name" name="name" type="text" value="{{ old('name', $event->name) }}" required class="{{ $input }}">
        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="location" class="mb-1 block text-sm font-medium">Lokasi</label>
        <input id="location" name="location" type="text" value="{{ old('location', $event->location) }}" class="{{ $input }}">
        @error('location')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="starts_at" class="mb-1 block text-sm font-medium">Waktu mulai <span class="text-red-500">*</span></label>
            <input id="starts_at" name="starts_at" type="datetime-local" required
                   value="{{ old('starts_at', $event->starts_at?->format('Y-m-d\TH:i')) }}" class="{{ $input }}">
            @error('starts_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="ends_at" class="mb-1 block text-sm font-medium">Waktu selesai</label>
            <input id="ends_at" name="ends_at" type="datetime-local"
                   value="{{ old('ends_at', $event->ends_at?->format('Y-m-d\TH:i')) }}" class="{{ $input }}">
            @error('ends_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            <p class="mt-1 text-xs text-slate-500">Check-in otomatis ditutup saat waktu selesai tiba. Kosongkan jika ingin menutup manual.</p>
        </div>
    </div>

    <div>
        <label for="status" class="mb-1 block text-sm font-medium">Status <span class="text-red-500">*</span></label>
        <select id="status" name="status" required class="{{ $input }}">
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected($currentStatus === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-slate-500">Absensi hanya bisa dilakukan saat status “Dibuka” dan sebelum waktu selesai.</p>
        @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="description" class="mb-1 block text-sm font-medium">Deskripsi</label>
        <textarea id="description" name="description" rows="4" class="{{ $input }}">{{ old('description', $event->description) }}</textarea>
        @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Simpan</button>
    <a href="{{ $cancelUrl }}" class="text-sm text-slate-600 hover:text-slate-900">Batal</a>
</div>
