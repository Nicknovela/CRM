@props(['label', 'value', 'sub' => null, 'color' => 'indigo', 'icon' => null])

@php
    $colors = [
        'indigo' => 'bg-indigo-50 text-indigo-600',
        'green' => 'bg-green-50 text-green-600',
        'yellow' => 'bg-yellow-50 text-yellow-600',
        'red' => 'bg-red-50 text-red-600',
        'blue' => 'bg-blue-50 text-blue-600',
    ];
@endphp

<div class="bg-white rounded-xl border border-gray-200 p-5 flex items-start gap-4">
    @if ($icon)
    <div class="flex-shrink-0 w-10 h-10 rounded-lg {{ $colors[$color] ?? $colors['indigo'] }} flex items-center justify-center">
        {!! $icon !!}
    </div>
    @endif
    <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-gray-500 truncate">{{ $label }}</p>
        <p class="mt-1 text-2xl font-bold text-gray-900 truncate">{{ $value }}</p>
        @if ($sub)
            <p class="mt-1 text-xs text-gray-400">{{ $sub }}</p>
        @endif
    </div>
</div>
