@vite(['resources/js/kanban.js'])
<div class="space-y-4">
    @if (session('success'))
        <div class="rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Selector de verticales --}}
    @if ($verticals->isNotEmpty())
        <div class="flex items-center gap-1 overflow-x-auto pb-1">
            @foreach ($verticals as $vertical)
                <button
                    wire:click="selectVertical({{ $vertical->id }})"
                    class="inline-flex items-center gap-1.5 rounded-full px-4 py-1.5 text-sm font-medium transition whitespace-nowrap
                        {{ $selectedVerticalId === $vertical->id
                            ? 'bg-indigo-600 text-white shadow-sm'
                            : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                    <span class="inline-block w-2 h-2 rounded-full flex-shrink-0"
                          style="background-color: {{ $vertical->color }};"></span>
                    {{ $vertical->name }}
                </button>
            @endforeach
        </div>
    @else
        <div class="rounded-lg bg-amber-50 border border-amber-100 px-4 py-3 text-sm text-amber-700">
            No hay verticales activas. Crea una vertical en la sección de administración.
        </div>
    @endif

    {{-- Tablero Kanban --}}
    @if ($selectedVerticalId && $this->stages->isNotEmpty())
        <div
            id="kanban-board"
            class="flex gap-4 overflow-x-auto kanban-scroll pb-4"
            wire:loading.class="opacity-50"
            wire:target="selectVertical,dealMoved">

            @foreach ($this->stages as $stage)
                @php
                    $stageDeals = $stage->deals ?? collect();
                    $totalAmount = $stageDeals->sum('amount');
                    $firstDealCurrency = $stageDeals->first()?->currency ?? 'BOB';
                @endphp
                <div
                    data-kanban-column="1"
                    data-stage-id="{{ $stage->id }}"
                    wire:key="col-{{ $stage->id }}"
                    class="flex-shrink-0 w-72 flex flex-col rounded-xl bg-gray-50 border border-gray-200">

                    {{-- Encabezado de columna --}}
                    <div class="px-4 pt-4 pb-3 border-b border-gray-200">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="inline-block w-3 h-3 rounded-full flex-shrink-0"
                                      style="background-color: {{ $stage->color }};"></span>
                                <h3 class="text-sm font-semibold text-gray-700 truncate">{{ $stage->name }}</h3>
                            </div>
                            <span class="inline-flex items-center justify-center rounded-full bg-gray-200 text-gray-600 text-xs font-bold w-5 h-5 flex-shrink-0">
                                {{ $stageDeals->count() }}
                            </span>
                        </div>
                        @if ($stageDeals->isNotEmpty())
                            <p class="text-xs text-gray-400 mt-1">
                                {{ format_currency($totalAmount, $firstDealCurrency) }}
                            </p>
                        @endif
                    </div>

                    {{-- Tarjetas de negocios --}}
                    <div
                        class="flex-1 p-3 space-y-2 min-h-24 kanban-column-body"
                        data-stage-id="{{ $stage->id }}">

                        @forelse ($stageDeals as $deal)
                            <div
                                data-deal-id="{{ $deal->id }}"
                                wire:key="deal-{{ $deal->id }}"
                                class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm cursor-grab active:cursor-grabbing hover:shadow-md transition-shadow group">

                                <div class="flex items-start justify-between gap-2">
                                    <a href="{{ route('deals.show', $deal) }}"
                                        class="text-sm font-medium text-gray-800 hover:text-indigo-600 leading-snug line-clamp-2 flex-1"
                                        onclick="event.stopPropagation()">
                                        {{ $deal->title }}
                                    </a>
                                </div>

                                @if ($deal->client)
                                    <p class="text-xs text-gray-500 mt-1.5 truncate">
                                        {{ $deal->client->name }}
                                        @if ($deal->client->company_name)
                                            &middot; {{ $deal->client->company_name }}
                                        @endif
                                    </p>
                                @endif

                                <div class="flex items-center justify-between mt-2.5 gap-2">
                                    <span class="text-xs font-semibold text-gray-700">
                                        {{ format_currency($deal->amount, $deal->currency) }}
                                    </span>
                                    <div class="flex items-center gap-2">
                                        @if ($deal->expected_close_date)
                                            <span class="text-xs text-gray-400">
                                                {{ format_date($deal->expected_close_date) }}
                                            </span>
                                        @endif
                                        @if ($deal->assignedTo)
                                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold flex-shrink-0"
                                                  title="{{ $deal->assignedTo->name }}">
                                                {{ mb_strtoupper(mb_substr($deal->assignedTo->name, 0, 1)) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @if ($deal->probability > 0)
                                    <div class="mt-2">
                                        <div class="w-full bg-gray-100 rounded-full h-1">
                                            <div class="h-1 rounded-full bg-indigo-400 transition-all"
                                                 style="width: {{ $deal->probability }}%;"></div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="flex items-center justify-center h-16 rounded-lg border-2 border-dashed border-gray-200 text-xs text-gray-400 kanban-empty-column">
                                Sin negocios
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    @elseif ($selectedVerticalId)
        <div class="flex items-center justify-center h-40 rounded-xl border-2 border-dashed border-gray-200">
            <p class="text-sm text-gray-400">Esta vertical no tiene etapas configuradas.</p>
        </div>
    @endif

    {{-- Loading overlay --}}
    <div wire:loading wire:target="selectVertical" class="fixed bottom-4 right-4 z-50">
        <div class="bg-white rounded-lg shadow-lg border border-gray-200 px-4 py-2 flex items-center gap-2 text-sm text-gray-600">
            <svg class="animate-spin w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            Cargando...
        </div>
    </div>
</div>
