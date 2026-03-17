<div class="relative" x-data="{ open: @entangle('showDropdown') }">
    <button
        @click="$wire.showDropdown = !$wire.showDropdown"
        class="relative p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
        aria-label="Notificaciones"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if ($unreadCount > 0)
        <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center">
            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
        </span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div
        x-show="open"
        @click.outside="open = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-200 z-50"
        x-cloak
    >
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
            <h4 class="text-sm font-semibold text-gray-900">Notificaciones</h4>
            @if ($unreadCount > 0)
            <button wire:click="markAllRead" class="text-xs text-indigo-600 hover:underline">Marcar todas como leídas</button>
            @endif
        </div>

        <div class="divide-y divide-gray-50 max-h-72 overflow-y-auto">
            @forelse ($notifications as $notification)
            <div class="px-4 py-3 hover:bg-gray-50 transition-colors {{ $notification->read_at ? 'opacity-60' : '' }}">
                <p class="text-sm text-gray-800">{{ $notification->data['message'] ?? 'Notificación' }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ format_datetime($notification->created_at) }}</p>
            </div>
            @empty
            <div class="px-4 py-8 text-center text-sm text-gray-400">
                Sin notificaciones
            </div>
            @endforelse
        </div>
    </div>
</div>
