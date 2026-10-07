@if($lastResult)
    @if($lastResult['success'])
        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 inline-flex h-6 w-6 flex-none items-center justify-center rounded-full bg-emerald-600 text-xs font-bold text-white">✓</span>
                <div>
                    <div class="font-semibold text-emerald-900">Check-in berhasil, {{ $lastResult['participant_name'] }}!</div>
                    @if(!empty($lastResult['participant_organization']))
                        <div class="text-emerald-700">{{ $lastResult['participant_organization'] }}</div>
                    @endif
                    @if(!empty($lastResult['checked_in_at']))
                        <div class="mt-1 text-emerald-700">
                            {{ $lastResult['checked_in_at'] }}
                            @if(!empty($lastResult['method_label']))
                                · {{ $lastResult['method_label'] }}
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 inline-flex h-6 w-6 flex-none items-center justify-center rounded-full bg-red-600 text-xs font-bold text-white">✕</span>
                <ul class="list-inside list-disc space-y-0.5 text-red-700">
                    @foreach(($lastResult['errors'] ?? []) as $messages)
                        @foreach($messages as $m)
                            <li>{{ $m }}</li>
                        @endforeach
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
@endif
