<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($deal)
        {{-- Cabecera del negocio --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        @if ($deal->stage)
                            <span class="inline-block w-3 h-3 rounded-full flex-shrink-0"
                                  style="background-color: {{ $deal->stage->color }};"></span>
                        @endif
                        <h2 class="text-xl font-bold text-gray-800 truncate">{{ $deal->title }}</h2>
                        @if ($deal->stage?->is_won)
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Ganado</span>
                        @elseif ($deal->stage?->is_lost)
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Perdido</span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $deal->vertical?->name ?? '—' }}
                        @if ($deal->stage) &middot; {{ $deal->stage->name }} @endif
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a href="{{ route('deals.edit', $deal) }}"
                        class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a4 4 0 01-2.828 1.172H7v-2a4 4 0 011.172-2.828z"/>
                        </svg>
                        Editar
                    </a>
                </div>
            </div>

            {{-- Datos clave en grid --}}
            <div class="mt-5 grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Monto</p>
                    <p class="mt-1 text-lg font-bold text-gray-800">
                        {{ format_currency($deal->amount, $deal->currency) }}
                    </p>
                </div>
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Probabilidad</p>
                    <p class="mt-1 text-lg font-bold text-gray-800">{{ $deal->probability }}%</p>
                </div>
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Cierre estimado</p>
                    <p class="mt-1 text-sm font-semibold text-gray-700">
                        {{ $deal->expected_close_date ? format_date($deal->expected_close_date) : '—' }}
                    </p>
                </div>
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Responsable</p>
                    <p class="mt-1 text-sm font-semibold text-gray-700">{{ $deal->assignedTo?->name ?? '—' }}</p>
                </div>
            </div>

            {{-- Cliente --}}
            @if ($deal->client)
                <div class="mt-4 flex items-center gap-2 text-sm text-gray-600">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <a href="{{ route('clients.show', $deal->client) }}" class="hover:text-indigo-600 font-medium">
                        {{ $deal->client->name }}
                    </a>
                    @if ($deal->client->company_name)
                        <span class="text-gray-400">&mdash; {{ $deal->client->company_name }}</span>
                    @endif
                </div>
            @endif

            @if ($deal->commission_rate)
                <div class="mt-2 text-sm text-gray-600">
                    <span class="font-medium">Comisión:</span> {{ $deal->commission_rate }}%
                </div>
            @endif

            @if ($deal->notes)
                <div class="mt-4 rounded-lg bg-amber-50 border border-amber-100 px-4 py-3 text-sm text-gray-700">
                    <p class="text-xs font-semibold text-amber-500 uppercase tracking-wide mb-1">Notas</p>
                    <p>{{ $deal->notes }}</p>
                </div>
            @endif

            @if ($deal->stage?->is_lost && $deal->lost_reason)
                <div class="mt-4 rounded-lg bg-red-50 border border-red-100 px-4 py-3 text-sm text-red-700">
                    <p class="text-xs font-semibold text-red-400 uppercase tracking-wide mb-1">Razón de pérdida</p>
                    <p>{{ $deal->lost_reason }}</p>
                </div>
            @endif
        </div>

        {{-- Actividades --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">Actividades</h3>
                <button wire:click="$toggle('showActivityForm')"
                    class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Registrar actividad
                </button>
            </div>

            {{-- Formulario nueva actividad --}}
            @if ($showActivityForm)
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
                    <form wire:submit.prevent="saveActivity" class="space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
                                <select wire:model="activityForm.type"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="note">Nota</option>
                                    <option value="call">Llamada</option>
                                    <option value="email">Correo</option>
                                    <option value="meeting">Reunión</option>
                                </select>
                                @error('activityForm.type') <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-3">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Descripción <span class="text-red-500">*</span></label>
                                <textarea wire:model="activityForm.description" rows="2"
                                    placeholder="Describe la actividad realizada..."
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                @error('activityForm.description') <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" wire:click="$set('showActivityForm', false)"
                                class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-white transition">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700 transition">
                                <span wire:loading.remove wire:target="saveActivity">Guardar</span>
                                <span wire:loading wire:target="saveActivity">Guardando...</span>
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Lista de actividades --}}
            @if ($deal->activities->isEmpty())
                <div class="px-5 py-10 text-center text-sm text-gray-400">
                    No hay actividades registradas para este negocio.
                </div>
            @else
                <div class="divide-y divide-gray-50">
                    @foreach ($deal->activities as $activity)
                        <div class="px-5 py-4 flex gap-3" wire:key="activity-{{ $activity->id }}">
                            @php
                                $typeIcons = [
                                    'note'         => ['icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z', 'color' => 'bg-gray-100 text-gray-500'],
                                    'call'         => ['icon' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498A1 1 0 0121 15.72V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z', 'color' => 'bg-blue-100 text-blue-500'],
                                    'email'        => ['icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'color' => 'bg-purple-100 text-purple-500'],
                                    'meeting'      => ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color' => 'bg-green-100 text-green-500'],
                                    'stage_change' => ['icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4', 'color' => 'bg-amber-100 text-amber-500'],
                                ];
                                $typeData = $typeIcons[$activity->type] ?? $typeIcons['note'];
                                $typeLabels = ['note' => 'Nota', 'call' => 'Llamada', 'email' => 'Correo', 'meeting' => 'Reunión', 'stage_change' => 'Cambio de etapa'];
                            @endphp
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full {{ $typeData['color'] }} flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $typeData['icon'] }}"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                        {{ $typeLabels[$activity->type] ?? $activity->type }}
                                    </span>
                                    <span class="text-xs text-gray-400 flex-shrink-0">
                                        {{ format_date($activity->created_at) }}
                                        @if ($activity->user)
                                            &middot; {{ $activity->user->name }}
                                        @endif
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-gray-700">{{ $activity->description }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>
