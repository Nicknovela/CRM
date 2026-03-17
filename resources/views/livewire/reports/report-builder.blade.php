<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Panel de filtros --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Parámetros del reporte</h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Tipo de reporte --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tipo de reporte</label>
                <select wire:model.live="reportType"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="pipeline">Pipeline</option>
                    <option value="won">Negocios ganados</option>
                    <option value="lost">Negocios perdidos</option>
                    <option value="by_assignee">Por responsable</option>
                    <option value="by_vertical">Por vertical</option>
                    <option value="commissions">Comisiones</option>
                </select>
            </div>

            {{-- Vertical --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Vertical</label>
                <select wire:model.live="verticalId"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todas</option>
                    @foreach ($verticals as $vertical)
                        <option value="{{ $vertical->id }}">{{ $vertical->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Fecha desde --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Fecha desde</label>
                <input type="date" wire:model.live="dateFrom"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
            </div>

            {{-- Fecha hasta --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Fecha hasta</label>
                <input type="date" wire:model.live="dateTo"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
            </div>
        </div>

        {{-- Botones de exportación --}}
        <div class="mt-5 flex items-center gap-3 pt-4 border-t border-gray-100">
            <span class="text-xs text-gray-500 font-medium">Exportar:</span>
            <button wire:click="exportExcel"
                wire:loading.attr="disabled"
                wire:target="exportExcel"
                class="inline-flex items-center gap-1.5 rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 transition disabled:opacity-60">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span wire:loading.remove wire:target="exportExcel">Excel</span>
                <span wire:loading wire:target="exportExcel">Generando...</span>
            </button>
            <button wire:click="exportPdf"
                wire:loading.attr="disabled"
                wire:target="exportPdf"
                class="inline-flex items-center gap-1.5 rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition disabled:opacity-60">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <span wire:loading.remove wire:target="exportPdf">PDF</span>
                <span wire:loading wire:target="exportPdf">Generando...</span>
            </button>
        </div>
    </div>

    {{-- Resumen de parámetros seleccionados --}}
    <div class="bg-indigo-50 border border-indigo-100 rounded-xl px-5 py-4">
        <div class="flex flex-wrap items-center gap-3 text-sm text-indigo-700">
            <svg class="w-4 h-4 flex-shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>
                Reporte: <strong>{{ match($reportType) {
                    'pipeline'     => 'Pipeline',
                    'won'          => 'Negocios ganados',
                    'lost'         => 'Negocios perdidos',
                    'by_assignee'  => 'Por responsable',
                    'by_vertical'  => 'Por vertical',
                    'commissions'  => 'Comisiones',
                    default        => ucfirst($reportType),
                } }}</strong>
            </span>
            @if ($dateFrom)
                <span>&middot; Desde: <strong>{{ format_date($dateFrom) }}</strong></span>
            @endif
            @if ($dateTo)
                <span>&middot; Hasta: <strong>{{ format_date($dateTo) }}</strong></span>
            @endif
            @if ($verticalId)
                <span>&middot; Vertical: <strong>{{ $verticals->firstWhere('id', $verticalId)?->name ?? '—' }}</strong></span>
            @endif
        </div>
    </div>

    {{-- Instrucciones de vista previa --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
        <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="text-sm font-medium text-gray-500">Configura los parámetros y exporta el reporte</p>
        <p class="text-xs text-gray-400 mt-1">
            Selecciona el tipo, rango de fechas y vertical, luego haz clic en Excel o PDF para descargar.
        </p>
    </div>
</div>
