<div class="space-y-5">
    @if (session('success'))
        <div class="rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Barra de filtros --}}
    <div class="flex flex-wrap items-end gap-3 justify-between">
        <div class="flex flex-wrap items-end gap-3">
            {{-- Búsqueda --}}
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar negocios..."
                    class="pl-9 w-52 rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
            </div>

            {{-- Filtro vertical --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Vertical</label>
                <select wire:model.live="filterVertical"
                    class="rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todas</option>
                    @foreach ($verticals as $vertical)
                        <option value="{{ $vertical->id }}">{{ $vertical->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filtro etapa --}}
            @if ($filterVertical && $stages->isNotEmpty())
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Etapa</label>
                    <select wire:model.live="filterStage"
                        class="rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Todas</option>
                        @foreach ($stages as $stage)
                            <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Filtro asignado --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Responsable</label>
                <select wire:model.live="filterAssignee"
                    class="rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos</option>
                    @foreach ($assignees as $assignee)
                        <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <a href="{{ route('deals.create') }}"
            class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo Negocio
        </a>
    </div>

    {{-- Tabla de negocios --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left">
                            <button wire:click="sortBy('title')" class="flex items-center gap-1 text-xs font-semibold text-gray-500 uppercase tracking-wide hover:text-gray-700">
                                Título {{ $sortField === 'title' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' }}
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Cliente</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Vertical / Etapa</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Responsable</th>
                        <th class="px-4 py-3 text-left">
                            <button wire:click="sortBy('amount')" class="flex items-center gap-1 text-xs font-semibold text-gray-500 uppercase tracking-wide hover:text-gray-700">
                                Monto {{ $sortField === 'amount' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' }}
                            </button>
                        </th>
                        <th class="px-4 py-3 text-left">
                            <button wire:click="sortBy('expected_close_date')" class="flex items-center gap-1 text-xs font-semibold text-gray-500 uppercase tracking-wide hover:text-gray-700">
                                Cierre Est. {{ $sortField === 'expected_close_date' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' }}
                            </button>
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($deals as $deal)
                        <tr class="hover:bg-gray-50 transition" wire:key="deal-{{ $deal->id }}">
                            <td class="px-4 py-3">
                                <a href="{{ route('deals.show', $deal) }}" class="font-medium text-gray-800 hover:text-indigo-600">
                                    {{ $deal->title }}
                                </a>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $deal->probability }}% de cierre</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                @if ($deal->client)
                                    <a href="{{ route('clients.show', $deal->client) }}" class="hover:text-indigo-600">
                                        {{ $deal->client->name }}
                                    </a>
                                    @if ($deal->client->company_name)
                                        <p class="text-xs text-gray-400">{{ $deal->client->company_name }}</p>
                                    @endif
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs text-gray-500">{{ $deal->vertical?->name ?? '—' }}</span>
                                @if ($deal->stage)
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="inline-block w-2 h-2 rounded-full flex-shrink-0"
                                              style="background-color: {{ $deal->stage->color }};"></span>
                                        <span class="text-xs text-gray-700">{{ $deal->stage->name }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 text-sm">{{ $deal->assignedTo?->name ?? '—' }}</td>
                            <td class="px-4 py-3 font-semibold text-gray-700">
                                {{ format_currency($deal->amount, $deal->currency) }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                {{ $deal->expected_close_date ? format_date($deal->expected_close_date) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('deals.edit', $deal) }}"
                                        class="p-1.5 rounded text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a4 4 0 01-2.828 1.172H7v-2a4 4 0 011.172-2.828z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-400">
                                No se encontraron negocios.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $deals->links() }}
        </div>
    </div>
</div>
