<div class="space-y-6">
    {{-- Flash messages --}}
    @if (session('success'))
        <div class="rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-800">Verticales de Negocio</h2>
        <button wire:click="showCreateForm"
            class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nueva Vertical
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Columna izquierda: lista de verticales --}}
        <div class="space-y-3">
            @forelse ($verticals as $vertical)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex items-start justify-between gap-4
                    {{ $selectedVerticalId === $vertical['id'] ? 'ring-2 ring-indigo-400' : '' }}">
                    <button wire:click="selectVertical({{ $vertical['id'] }})" class="flex items-center gap-3 flex-1 text-left">
                        <span class="inline-block w-3 h-3 rounded-full flex-shrink-0"
                              style="background-color: {{ $vertical['color'] }};"></span>
                        <div>
                            <p class="font-medium text-gray-800 text-sm">{{ $vertical['name'] }}</p>
                            @if ($vertical['description'])
                                <p class="text-xs text-gray-500 mt-0.5">{{ $vertical['description'] }}</p>
                            @endif
                            <p class="text-xs text-gray-400 mt-1">
                                {{ $vertical['deals_count'] ?? 0 }} negocios
                                @if ($vertical['track_commission'])
                                    &middot; <span class="text-indigo-500">Comisión activa</span>
                                @endif
                            </p>
                        </div>
                    </button>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <button wire:click="edit({{ $vertical['id'] }})"
                            class="p-1.5 rounded text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a4 4 0 01-2.828 1.172H7v-2a4 4 0 011.172-2.828z"/>
                            </svg>
                        </button>
                        @if ($confirmingDeleteId === $vertical['id'])
                            <div class="flex items-center gap-1">
                                <span class="text-xs text-red-600">¿Confirmar?</span>
                                <button wire:click="delete({{ $vertical['id'] }})"
                                    class="text-xs text-red-600 font-semibold hover:underline">Sí</button>
                                <button wire:click="$set('confirmingDeleteId', null)"
                                    class="text-xs text-gray-500 hover:underline">No</button>
                            </div>
                        @else
                            <button wire:click="confirmDelete({{ $vertical['id'] }})"
                                class="p-1.5 rounded text-gray-400 hover:text-red-600 hover:bg-red-50 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-8">No hay verticales creadas.</p>
            @endforelse
        </div>

        {{-- Columna derecha: etapas de la vertical seleccionada --}}
        <div>
            @if ($selectedVertical)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4">
                    <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                        <span class="inline-block w-2.5 h-2.5 rounded-full"
                              style="background-color: {{ $selectedVertical->color }};"></span>
                        Etapas de {{ $selectedVertical->name }}
                    </h3>

                    <ul id="stages-list-{{ $selectedVertical->id }}"
                        class="space-y-2"
                        x-data="stageReorder(@js($selectedVertical->stages->pluck('id')->toArray()))"
                        x-init="init()">
                        @foreach ($selectedVertical->stages as $stage)
                            <li class="flex items-center gap-2 bg-gray-50 rounded-lg px-3 py-2 cursor-grab active:cursor-grabbing"
                                data-id="{{ $stage->id }}">
                                <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                                <span class="inline-block w-2.5 h-2.5 rounded-full flex-shrink-0"
                                      style="background-color: {{ $stage->color }};"></span>
                                <span class="flex-1 text-sm text-gray-700">{{ $stage->name }}</span>
                                @if ($stage->is_won)
                                    <span class="text-xs text-green-600 font-medium">Ganado</span>
                                @elseif ($stage->is_lost)
                                    <span class="text-xs text-red-600 font-medium">Perdido</span>
                                @endif
                                <button wire:click="deleteStage({{ $stage->id }})"
                                    class="p-1 rounded text-gray-300 hover:text-red-500 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    {{-- Formulario nueva etapa --}}
                    <form wire:submit.prevent="saveStage" class="flex items-end gap-2 pt-2 border-t border-gray-100">
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Nombre de etapa</label>
                            <input type="text" wire:model="stageForm.name" placeholder="Ej: Propuesta enviada"
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                            @error('stageForm.name') <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Color</label>
                            <input type="color" wire:model="stageForm.color"
                                class="h-9 w-12 rounded border-gray-300 cursor-pointer"/>
                        </div>
                        <button type="submit"
                            class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition">
                            Agregar
                        </button>
                    </form>
                </div>
            @else
                <div class="flex items-center justify-center h-40 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                    <p class="text-sm text-gray-400">Selecciona una vertical para gestionar sus etapas</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal formulario vertical --}}
    @if ($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4">
                <h3 class="text-base font-semibold text-gray-800">
                    {{ $editingId ? 'Editar Vertical' : 'Nueva Vertical' }}
                </h3>

                <form wire:submit.prevent="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="form.name"
                            class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"/>
                        @error('form.name') <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                        <textarea wire:model="form.description" rows="2"
                            class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>

                    <div class="flex items-end gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                            <input type="color" wire:model="form.color"
                                class="h-9 w-full rounded border-gray-300 cursor-pointer"/>
                        </div>
                        <div class="flex items-center gap-2 pb-1">
                            <input type="checkbox" id="track-commission" wire:model="form.track_commission"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"/>
                            <label for="track-commission" class="text-sm text-gray-700">Rastrear comisión</label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showForm', false)"
                            class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition">
                            <span wire:loading.remove wire:target="save">Guardar</span>
                            <span wire:loading wire:target="save">Guardando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
