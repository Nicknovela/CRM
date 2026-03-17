<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($client)
        {{-- Cabecera del cliente --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center text-lg font-bold text-indigo-700 flex-shrink-0">
                        {{ mb_strtoupper(mb_substr($client->name, 0, 1)) }}
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">{{ $client->name }}</h2>
                        @if ($client->company_name)
                            <p class="text-sm text-gray-500">{{ $client->company_name }}</p>
                        @endif
                        @if ($client->industry)
                            <p class="text-xs text-gray-400 mt-0.5">{{ $client->industry }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a href="{{ route('clients.edit', $client) }}"
                        class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a4 4 0 01-2.828 1.172H7v-2a4 4 0 011.172-2.828z"/>
                        </svg>
                        Editar
                    </a>
                    <a href="{{ route('deals.create', ['client_id' => $client->id]) }}"
                        class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nuevo Negocio
                    </a>
                </div>
            </div>

            {{-- Datos de contacto --}}
            <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @if ($client->email)
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <a href="mailto:{{ $client->email }}" class="hover:text-indigo-600 truncate">{{ $client->email }}</a>
                    </div>
                @endif
                @if ($client->phone)
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498A1 1 0 0121 15.72V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span>{{ $client->phone }}</span>
                    </div>
                @endif
                @if ($client->website)
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                        <a href="{{ $client->website }}" target="_blank" rel="noopener" class="hover:text-indigo-600 truncate">
                            {{ $client->website }}
                        </a>
                    </div>
                @endif
                @if ($client->address)
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="truncate">{{ $client->address }}</span>
                    </div>
                @endif
            </div>

            @if ($client->notes)
                <div class="mt-4 rounded-lg bg-gray-50 border border-gray-100 px-4 py-3 text-sm text-gray-600">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Notas</p>
                    <p>{{ $client->notes }}</p>
                </div>
            @endif
        </div>

        {{-- Negocios del cliente --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">
                    Negocios
                    <span class="ml-1.5 inline-flex items-center justify-center rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold w-5 h-5">
                        {{ $client->deals->count() }}
                    </span>
                </h3>
            </div>

            @if ($client->deals->isEmpty())
                <div class="px-5 py-10 text-center text-sm text-gray-400">
                    Este cliente no tiene negocios registrados.
                </div>
            @else
                <div class="divide-y divide-gray-50">
                    @foreach ($client->deals as $deal)
                        <div class="px-5 py-4 flex items-center justify-between gap-4 hover:bg-gray-50 transition">
                            <div class="flex items-center gap-3 min-w-0">
                                @if ($deal->stage)
                                    <span class="inline-block w-2.5 h-2.5 rounded-full flex-shrink-0"
                                          style="background-color: {{ $deal->stage->color }};"></span>
                                @endif
                                <div class="min-w-0">
                                    <a href="{{ route('deals.show', $deal) }}"
                                        class="font-medium text-gray-800 hover:text-indigo-600 truncate block text-sm">
                                        {{ $deal->title }}
                                    </a>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ $deal->vertical?->name ?? '—' }}
                                        @if ($deal->stage) &middot; {{ $deal->stage->name }} @endif
                                        @if ($deal->assignedTo) &middot; {{ $deal->assignedTo->name }} @endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 flex-shrink-0">
                                <span class="text-sm font-semibold text-gray-700">
                                    {{ format_currency($deal->amount, $deal->currency) }}
                                </span>
                                @if ($deal->expected_close_date)
                                    <span class="text-xs text-gray-400">{{ format_date($deal->expected_close_date) }}</span>
                                @endif
                                @if ($deal->stage?->is_won)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Ganado</span>
                                @elseif ($deal->stage?->is_lost)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Perdido</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                        {{ $deal->probability }}%
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>
