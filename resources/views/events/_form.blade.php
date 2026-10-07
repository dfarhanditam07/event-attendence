@php
    $inputClasses = 'w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-enc-600 focus:outline-none focus:ring-2 focus:ring-enc-600/10 transition-colors';
    $currentStatus = old('status', $event->status?->value);
@endphp

@csrf

<div class="space-y-6">
    <!-- Bagian 1: Informasi Dasar -->
    <div>
        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2 mb-4">
            Informasi Dasar Acara
        </h3>
        <div class="space-y-4">
            <div>
                <label for="name" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700">
                    Nama Event <span class="text-rose-500">*</span>
                </label>
                <input id="name" name="name" type="text" value="{{ old('name', $event->name) }}" required
                       placeholder="Contoh: Rapat Kerja Nasional 2026 / Townhall Q4"
                       class="{{ $inputClasses }}">
                @error('name')
                    <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="location" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700">
                    Lokasi Pelaksanaan
                </label>
                <input id="location" name="location" type="text" value="{{ old('location', $event->location) }}"
                       placeholder="Contoh: Grand Ballroom Hotel Indonesia Kempinski / Ruang Serbaguna Lt. 5"
                       class="{{ $inputClasses }}">
                @error('location')
                    <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700">
                    Deskripsi / Catatan Acara
                </label>
                <textarea id="description" name="description" rows="3"
                          placeholder="Tambahkan informasi singkat mengenai agenda, aturan berpakaian, atau instruksi kehadiran..."
                          class="{{ $inputClasses }}">{{ old('description', $event->description) }}</textarea>
                @error('description')
                    <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <!-- Bagian 2: Jadwal & Status -->
    <div class="pt-2">
        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2 mb-4">
            Jadwal & Status Pelaksanaan
        </h3>

        <div class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="starts_at" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Waktu Mulai <span class="text-rose-500">*</span>
                    </label>
                    <input id="starts_at" name="starts_at" type="datetime-local" required
                           value="{{ old('starts_at', $event->starts_at?->format('Y-m-d\TH:i')) }}"
                           class="{{ $inputClasses }}">
                    @error('starts_at')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="ends_at" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Waktu Selesai <span class="text-xs font-normal normal-case text-slate-400">(Opsional)</span>
                    </label>
                    <input id="ends_at" name="ends_at" type="datetime-local"
                           value="{{ old('ends_at', $event->ends_at?->format('Y-m-d\TH:i')) }}"
                           class="{{ $inputClasses }}">
                    @error('ends_at')
                        <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-slate-50/70 p-3 text-xs text-slate-600 flex items-start gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-none text-slate-500 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="16" x2="12" y2="12"/>
                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                <span>Jika waktu selesai diisi, sesi check-in mandiri akan <strong>otomatis ditutup</strong> saat waktu selesai tercapai. Jika dikosongkan, penutupan dapat dilakukan manual lewat status event.</span>
            </div>

            <div>
                <label for="status" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-700">
                    Status Event <span class="text-rose-500">*</span>
                </label>
                <select id="status" name="status" required class="{{ $inputClasses }}">
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected($currentStatus === $status->value)>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1.5 text-xs text-slate-500">
                    Peserta hanya dapat melakukan check-in ketika status diset ke <strong>Dibuka</strong>.
                </p>
                @error('status')
                    <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>

<div class="mt-8 flex items-center gap-3 border-t border-slate-100 pt-6">
    <button type="submit"
            class="inline-flex items-center gap-2 rounded-xl bg-enc-800 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-enc-900 focus:outline-none focus:ring-2 focus:ring-enc-600/30">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"/>
        </svg>
        Simpan Event
    </button>
    <a href="{{ $cancelUrl }}"
       class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-xs transition hover:bg-slate-50">
        Batal
    </a>
</div>
