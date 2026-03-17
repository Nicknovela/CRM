<div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="px-5 py-4 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-700">Actividad Reciente</h3>
    </div>

    @if ($activities->isEmpty())
        <p class="px-5 py-8 text-center text-sm text-gray-400">Sin actividad reciente.</p>
    @else
        <ul class="divide-y divide-gray-50">
            @foreach ($activities as $activity)
                <li class="flex items-start gap-3 px-5 py-3">
                    {{-- Icono según tipo --}}
                    <div class="mt-0.5 flex-shrink-0">
                        @php
                            $iconMap = [
                                'note'        => ['bg' => 'bg-blue-100',   'text' => 'text-blue-600',   'svg' => 'M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z'],
                                'call'        => ['bg' => 'bg-green-100',  'text' => 'text-green-600',  'svg' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z'],
                                'meeting'     => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600', 'svg' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                                'stage_change'=> ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-600', 'svg' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
                                'email'       => ['bg' => 'bg-red-100',    'text' => 'text-red-600',    'svg' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                            ];
                            $icon = $iconMap[$activity->type] ?? $iconMap['note'];
                        @endphp
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full {{ $icon['bg'] }}">
                            <svg class="w-4 h-4 {{ $icon['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon['svg'] }}"/>
                            </svg>
                        </span>
                    </div>

                    {{-- Contenido --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-700 leading-snug">{{ $activity->description }}</p>
                        <p class="mt-0.5 text-xs text-gray-400">
                            <span class="font-medium text-gray-500">{{ $activity->user?->name ?? 'Sistema' }}</span>
                            &mdash;
                            {{ $activity->deal?->title }}
                            @if ($activity->deal?->client)
                                <span class="text-gray-400">({{ $activity->deal->client->name }})</span>
                            @endif
                        </p>
                    </div>

                    {{-- Fecha --}}
                    <time class="flex-shrink-0 text-xs text-gray-400 whitespace-nowrap"
                          datetime="{{ $activity->created_at->toIso8601String() }}"
                          title="{{ $activity->created_at->format('d/m/Y H:i') }}">
                        {{ $activity->created_at->diffForHumans() }}
                    </time>
                </li>
            @endforeach
        </ul>
    @endif
</div>
