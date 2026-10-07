@if($lastResult)
    @if($lastResult['success'])
        <div class="mb-6 rounded-2xl border border-emerald-300/80 bg-emerald-50/95 p-5 shadow-xs">
            <div class="flex items-start gap-3.5">
                <span class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-emerald-600 text-white shadow-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </span>
                <div class="flex-1">
                    <div class="text-xs font-bold uppercase tracking-wider text-emerald-800">
                        Presensi Berhasil Dikonfirmasi!
                    </div>
                    <div class="mt-0.5 text-base font-bold text-emerald-950">
                        {{ $lastResult['participant_name'] }}
                    </div>
                    @if(!empty($lastResult['participant_organization']))
                        <div class="text-xs font-medium text-emerald-800">
                            {{ $lastResult['participant_organization'] }}
                        </div>
                    @endif

                    <div class="mt-2.5 flex flex-wrap items-center gap-2 text-xs">
                        @if(!empty($lastResult['checked_in_at']))
                            <span class="inline-flex items-center gap-1 rounded-md bg-white/80 px-2.5 py-1 font-medium text-emerald-900 ring-1 ring-emerald-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                                {{ $lastResult['checked_in_at'] }}
                            </span>
                        @endif
                        @if(!empty($lastResult['method_label']))
                            <span class="inline-flex items-center rounded-md bg-white/80 px-2.5 py-1 font-medium text-emerald-900 ring-1 ring-emerald-200">
                                Metode: {{ $lastResult['method_label'] }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="mb-6 rounded-2xl border border-rose-300/80 bg-rose-50/95 p-5 shadow-xs">
            <div class="flex items-start gap-3.5">
                <span class="flex h-9 w-9 flex-none items-center justify-center rounded-xl bg-rose-600 text-white shadow-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </span>
                <div class="flex-1">
                    <div class="text-xs font-bold uppercase tracking-wider text-rose-800">
                        Check-in Tidak Berhasil
                    </div>
                    <ul class="mt-1 list-inside list-disc space-y-1 text-xs font-medium text-rose-800">
                        @foreach(($lastResult['errors'] ?? []) as $messages)
                            @foreach($messages as $m)
                                <li>{{ $m }}</li>
                            @endforeach
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif
@endif
