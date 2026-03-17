<?php

namespace Database\Seeders;

use App\Models\Stage;
use App\Models\Vertical;
use Illuminate\Database\Seeder;

class VerticalSeeder extends Seeder
{
    public function run(): void
    {
        $verticals = [
            [
                'name' => 'Consultoría',
                'color' => '#6366f1',
                'track_commission' => false,
                'stages' => [
                    ['name' => 'Lead', 'color' => '#94a3b8', 'position' => 1],
                    ['name' => 'Calificación', 'color' => '#60a5fa', 'position' => 2],
                    ['name' => 'Propuesta', 'color' => '#f59e0b', 'position' => 3],
                    ['name' => 'Negociación', 'color' => '#fb923c', 'position' => 4],
                    ['name' => 'Ganado', 'color' => '#22c55e', 'position' => 5, 'is_won' => true],
                    ['name' => 'Perdido', 'color' => '#ef4444', 'position' => 6, 'is_lost' => true],
                ],
            ],
            [
                'name' => 'Financiamiento',
                'color' => '#f59e0b',
                'track_commission' => true,
                'stages' => [
                    ['name' => 'Prospecto', 'color' => '#94a3b8', 'position' => 1],
                    ['name' => 'Análisis', 'color' => '#60a5fa', 'position' => 2],
                    ['name' => 'Comité', 'color' => '#a78bfa', 'position' => 3],
                    ['name' => 'Aprobación', 'color' => '#fb923c', 'position' => 4],
                    ['name' => 'Desembolso', 'color' => '#22c55e', 'position' => 5, 'is_won' => true],
                    ['name' => 'Rechazado', 'color' => '#ef4444', 'position' => 6, 'is_lost' => true],
                ],
            ],
            [
                'name' => 'Tecnología',
                'color' => '#10b981',
                'track_commission' => false,
                'stages' => [
                    ['name' => 'Contacto', 'color' => '#94a3b8', 'position' => 1],
                    ['name' => 'Demo', 'color' => '#60a5fa', 'position' => 2],
                    ['name' => 'POC', 'color' => '#a78bfa', 'position' => 3],
                    ['name' => 'Propuesta', 'color' => '#fb923c', 'position' => 4],
                    ['name' => 'Cierre', 'color' => '#22c55e', 'position' => 5, 'is_won' => true],
                    ['name' => 'Perdido', 'color' => '#ef4444', 'position' => 6, 'is_lost' => true],
                ],
            ],
        ];

        foreach ($verticals as $verticalData) {
            $stages = $verticalData['stages'];
            unset($verticalData['stages']);

            $vertical = Vertical::firstOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($verticalData['name'])],
                array_merge($verticalData, ['is_active' => true])
            );

            foreach ($stages as $stageData) {
                Stage::firstOrCreate(
                    ['vertical_id' => $vertical->id, 'name' => $stageData['name']],
                    array_merge($stageData, [
                        'vertical_id' => $vertical->id,
                        'is_won' => $stageData['is_won'] ?? false,
                        'is_lost' => $stageData['is_lost'] ?? false,
                    ])
                );
            }
        }
    }
}
