<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' — ' : '' }}{{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900" x-data>

    {{-- Toast container --}}
    <div
        x-data
        @toast.window="$store.toast.add($event.detail.message, $event.detail.type ?? 'success')"
        class="fixed top-4 right-4 z-50 space-y-2"
        style="width: 320px;"
        aria-live="polite"
    >
        <template x-for="msg in $store.toast.messages" :key="msg.id">
            <div
                x-show="true"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-x-8"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                :class="{
                    'bg-green-50 border-green-400 text-green-800': msg.type === 'success',
                    'bg-red-50 border-red-400 text-red-800': msg.type === 'error',
                    'bg-yellow-50 border-yellow-400 text-yellow-800': msg.type === 'warning',
                    'bg-blue-50 border-blue-400 text-blue-800': msg.type === 'info',
                }"
                class="flex items-start gap-3 p-4 rounded-lg border shadow-lg"
            >
                <div class="flex-1 text-sm font-medium" x-text="msg.message"></div>
                <button @click="$store.toast.remove(msg.id)" class="opacity-60 hover:opacity-100 text-lg leading-none">&times;</button>
            </div>
        </template>
    </div>

    <div class="flex h-screen overflow-hidden">

        {{-- Sidebar --}}
        <aside class="flex flex-col w-64 flex-shrink-0 bg-sidebar text-white overflow-y-auto" id="sidebar">
            {{-- Logo --}}
            <div class="flex items-center gap-3 px-5 py-4 border-b border-sidebar-border">
                <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center font-bold text-sm">CRM</div>
                <span class="font-semibold text-sm tracking-wide">{{ config('app.name') }}</span>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 px-3 py-4 space-y-1">
                {{-- Main --}}
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-2 mb-2">Principal</div>

                <x-sidebar-nav-item route="dashboard" icon="home">
                    Dashboard
                </x-sidebar-nav-item>

                <x-sidebar-nav-item route="kanban" icon="view-columns">
                    Kanban
                </x-sidebar-nav-item>

                <x-sidebar-nav-item route="deals.index" icon="briefcase">
                    Negocios
                </x-sidebar-nav-item>

                <x-sidebar-nav-item route="clients.index" icon="users">
                    Clientes
                </x-sidebar-nav-item>

                @can('reports.view')
                <x-sidebar-nav-item route="reports.index" icon="chart-bar">
                    Reportes
                </x-sidebar-nav-item>
                @endcan

                {{-- Admin --}}
                @role('admin|manager')
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-2 mt-6 mb-2">Configuración</div>

                @role('admin')
                <x-sidebar-nav-item route="admin.verticals.index" icon="tag">
                    Verticales
                </x-sidebar-nav-item>

                <x-sidebar-nav-item route="admin.users.index" icon="user-group">
                    Usuarios
                </x-sidebar-nav-item>
                @endrole
                @endrole
            </nav>

            {{-- User info at bottom --}}
            <div class="px-4 py-3 border-t border-sidebar-border">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-xs font-bold flex-shrink-0">
                        {{ auth()->user()->initials }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium truncate">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-slate-400 truncate">{{ auth()->user()->getRoleNames()->first() }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Cerrar sesión" class="text-slate-400 hover:text-white transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main content area --}}
        <div class="flex-1 flex flex-col overflow-hidden">

            {{-- Top bar --}}
            <header class="flex-shrink-0 bg-white border-b border-gray-200 px-6 py-3 flex items-center justify-between">
                <div>
                    {{ $header ?? '' }}
                </div>
                <div class="flex items-center gap-4">
                    @livewire('notification-bell')
                    <span class="text-xs text-gray-400">{{ format_datetime(now()) }}</span>
                </div>
            </header>

            {{-- Page content --}}
            <main class="flex-1 overflow-y-auto p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
