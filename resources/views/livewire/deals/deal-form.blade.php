<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    @if (session('success'))
        <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

            {{-- Título --}}
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Título del negocio <span class="text-red-500">*</span>
                </label>
                <input type="text" wire:model="form.title" placeholder="Ej: Implementación ERP Acme"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                @error('form.title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Cliente --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Cliente <span class="text-red-500">*</span>
                </label>
                <select wire:model.live="form.client_id"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Seleccionar cliente...</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}">
                            {{ $client->name }}{{ $client->company_name ? ' — ' . $client->company_name : '' }}
                        </option>
                    @endforeach
                </select>
                @error('form.client_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Responsable --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Responsable <span class="text-red-500">*</span>
                </label>
                <select wire:model="form.assigned_to"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Seleccionar responsable...</option>
                    @foreach ($assignees as $assignee)
                        <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                    @endforeach
                </select>
                @error('form.assigned_to') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Vertical --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Vertical <span class="text-red-500">*</span>
                </label>
                <select wire:model.live="form.vertical_id"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Seleccionar vertical...</option>
                    @foreach ($verticals as $vertical)
                        <option value="{{ $vertical->id }}">{{ $vertical->name }}</option>
                    @endforeach
                </select>
                @error('form.vertical_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Etapa --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Etapa <span class="text-red-500">*</span>
                </label>
                <select wire:model.live="form.stage_id"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"
                    @if (empty($stages)) disabled @endif>
                    <option value="">
                        {{ empty($stages) ? 'Selecciona una vertical primero...' : 'Seleccionar etapa...' }}
                    </option>
                    @foreach ($stages as $stage)
                        <option value="{{ $stage['id'] }}">
                            {{ $stage['name'] }}
                            @if ($stage['is_won']) (Ganado) @elseif ($stage['is_lost']) (Perdido) @endif
                        </option>
                    @endforeach
                </select>
                @error('form.stage_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Monto --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Monto <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-2">
                    <select wire:model="form.currency"
                        class="w-24 rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="BOB">BOB</option>
                        <option value="USD">USD</option>
                        <option value="EUR">EUR</option>
                    </select>
                    <input type="number" wire:model="form.amount" placeholder="0.00" min="0" step="0.01"
                        class="flex-1 rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                </div>
                @error('form.amount') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                @error('form.currency') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Probabilidad --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Probabilidad de cierre (%) <span class="text-red-500">*</span>
                </label>
                <input type="number" wire:model="form.probability" placeholder="0" min="0" max="100"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                @error('form.probability') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Fecha estimada de cierre --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha estimada de cierre</label>
                <input type="date" wire:model="form.expected_close_date"
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                @error('form.expected_close_date') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Comisión (condicional) --}}
            @if ($showCommission)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tasa de comisión (%)</label>
                    <input type="number" wire:model="form.commission_rate" placeholder="0.00" min="0" max="100" step="0.01"
                        class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                    @error('form.commission_rate') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            @endif

            {{-- Notas --}}
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                <textarea wire:model="form.notes" rows="4" placeholder="Información adicional sobre el negocio..."
                    class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                @error('form.notes') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
            <a href="{{ $dealId ? route('deals.show', $dealId) : route('deals.index') }}"
                class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                Cancelar
            </a>
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition">
                <span wire:loading.remove wire:target="save">
                    {{ $dealId ? 'Actualizar Negocio' : 'Crear Negocio' }}
                </span>
                <span wire:loading wire:target="save" class="flex items-center gap-2">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    Guardando...
                </span>
            </button>
        </div>
    </form>

    {{-- Modal razón de pérdida --}}
    @if ($showLostModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
                <h3 class="text-base font-semibold text-gray-800">Motivo de pérdida</h3>
                <p class="text-sm text-gray-500">
                    Esta etapa marca el negocio como perdido. Por favor indica el motivo.
                </p>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Razón <span class="text-red-500">*</span>
                    </label>
                    <textarea wire:model="lostReason" rows="3"
                        placeholder="Ej: Precio fuera de presupuesto del cliente..."
                        class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    @error('lostReason') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="cancelLost"
                        class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                    <button type="button" wire:click="save"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition">
                        <span wire:loading.remove wire:target="save">Confirmar pérdida</span>
                        <span wire:loading wire:target="save">Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
