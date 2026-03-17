<x-crm-layout>
    <x-slot name="title">Dashboard</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Dashboard</h2>
            <p class="text-sm text-gray-500">Bienvenido, {{ auth()->user()->name }}</p>
        </div>
    </x-slot>

    @livewire('dashboard.dashboard-metrics')

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            @livewire('dashboard.upcoming-deals')
        </div>
        <div>
            @livewire('dashboard.activity-feed')
        </div>
    </div>
</x-crm-layout>
