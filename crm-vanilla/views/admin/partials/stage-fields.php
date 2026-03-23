<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de la etapa *</label>
        <input type="text" name="name" required
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
        <input type="color" name="color" value="#6B7280"
               class="w-full h-9 border border-gray-300 rounded-lg cursor-pointer">
    </div>
    <div class="flex gap-4">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="is_won" value="1" class="rounded text-green-600">
            <span class="text-sm text-gray-700">✓ Etapa ganada</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="is_lost" value="1" class="rounded text-red-500">
            <span class="text-sm text-gray-700">✗ Etapa perdida</span>
        </label>
    </div>
</div>
