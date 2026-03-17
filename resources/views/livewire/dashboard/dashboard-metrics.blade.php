<div class="space-y-6">
    {{-- Filtros --}}
    <div class="flex flex-wrap items-center gap-4">
        <div>
            <label for="vertical-filter" class="block text-xs font-medium text-gray-500 mb-1">Vertical</label>
            <select id="vertical-filter" wire:model.live="verticalId"
                class="rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Todos</option>
                @foreach ($verticals as $vertical)
                    <option value="{{ $vertical->id }}">{{ $vertical->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="date-range" class="block text-xs font-medium text-gray-500 mb-1">Período</label>
            <select id="date-range" wire:model.live="dateRange"
                class="rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="7d">Últimos 7 días</option>
                <option value="30d">Últimos 30 días</option>
                <option value="90d">Últimos 90 días</option>
                <option value="365d">Último año</option>
            </select>
        </div>
    </div>

    {{-- Tarjetas de métricas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        {{-- Valor del pipeline --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Valor del Pipeline</p>
            <p class="mt-2 text-2xl font-bold text-gray-800">
                Bs. {{ number_format($pipelineValue, 2, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-gray-400">Negocios activos</p>
        </div>

        {{-- Tasa de conversión --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Tasa de Conversión</p>
            <p class="mt-2 text-2xl font-bold text-gray-800">{{ $conversionRate }}%</p>
            <p class="mt-1 text-xs text-gray-400">Ganados vs. cerrados</p>
        </div>

        {{-- Ticket promedio --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Ticket Promedio</p>
            <p class="mt-2 text-2xl font-bold text-gray-800">
                Bs. {{ number_format($averageTicket, 2, ',', '.') }}
            </p>
            <p class="mt-1 text-xs text-gray-400">Negocios ganados</p>
        </div>

        {{-- Tiempo promedio de cierre --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Tiempo Promedio Cierre</p>
            <p class="mt-2 text-2xl font-bold text-gray-800">
                {{ $avgCloseTime !== null ? number_format($avgCloseTime, 1) . ' días' : '—' }}
            </p>
            <p class="mt-1 text-xs text-gray-400">Desde creación hasta cierre</p>
        </div>
    </div>

    {{-- Gráfico de embudo --}}
    @if (count($funnelData) > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Embudo de Ventas</h3>
            <div x-data="funnelChart(@json($funnelData))" class="relative">
                <canvas x-ref="canvas" class="w-full" style="height: 220px;"></canvas>
            </div>
        </div>
    @endif
</div>
