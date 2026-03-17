<div class="bg-white rounded-xl shadow-sm border border-gray-100">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-700">Negocios por Cerrar</h3>
        <div class="flex items-center gap-2">
            <label for="days-filter" class="text-xs text-gray-400">Próximos</label>
            <select id="days-filter" wire:model.live="days"
                class="text-xs rounded border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 py-0.5">
                <option value="7">7 días</option>
                <option value="14">14 días</option>
                <option value="30">30 días</option>
            </select>
        </div>
    </div>

    @if ($deals->isEmpty())
        <p class="px-5 py-8 text-center text-sm text-gray-400">
            Sin negocios programados para cerrar en los próximos {{ $days }} días.
        </p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Negocio</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Cliente</th>
                        <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Monto</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Etapa</th>
                        <th class="px-4 py-2 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Días</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($deals as $deal)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-2.5 font-medium text-gray-800 max-w-xs truncate">
                                <a href="{{ route('deals.show', $deal) }}" class="hover:text-indigo-600">
                                    {{ $deal->title }}
                                </a>
                            </td>
                            <td class="px-4 py-2.5 text-gray-600">{{ $deal->client?->name ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-right font-medium text-gray-800">
                                {{ $deal->currency }} {{ number_format($deal->amount, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-2.5">
                                @if ($deal->stage)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium"
                                          style="background-color: {{ $deal->stage->color }}20; color: {{ $deal->stage->color }};">
                                        {{ $deal->stage->name }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                @php
                                    $days = $deal->days_remaining;
                                    $urgencyClass = $days <= 2
                                        ? 'bg-red-100 text-red-700'
                                        : ($days <= 5 ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700');
                                @endphp
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $urgencyClass }}">
                                    {{ $days === 0 ? 'Hoy' : $days . 'd' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
