<x-crm-layout>
    <x-slot name="title">Detalle de Cliente</x-slot>
    <x-slot name="header">
        <a href="{{ route('clients.index') }}" class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Clientes
        </a>
    </x-slot>

    @livewire('clients.client-detail', ['clientId' => $clientId])
</x-crm-layout>
