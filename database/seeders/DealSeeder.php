<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Deal;
use App\Models\Stage;
use App\Models\User;
use App\Models\Vertical;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DealSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@crm.test')->first();
        $manager = User::where('email', 'gerente@crm.test')->first();
        $vendedor = User::where('email', 'vendedor@crm.test')->first();

        $consultoria = Vertical::where('slug', 'consultoria')->first();
        $financiamiento = Vertical::where('slug', 'financiamiento')->first();
        $tecnologia = Vertical::where('slug', 'tecnologia')->first();

        $clients = Client::all();

        $deals = [
            [
                'title' => 'Consultoría Estratégica Q2',
                'client' => $clients->where('company_name', 'Industrias Vargas S.R.L.')->first(),
                'vertical' => $consultoria,
                'stage_name' => 'Propuesta',
                'amount' => 45000,
                'currency' => 'BOB',
                'probability' => 60,
                'assigned_to' => $admin->id,
                'expected_close_date' => Carbon::today()->addDays(30),
            ],
            [
                'title' => 'Crédito Capital de Trabajo',
                'client' => $clients->where('company_name', 'Agro Torrico Exportaciones')->first(),
                'vertical' => $financiamiento,
                'stage_name' => 'Comité',
                'amount' => 250000,
                'currency' => 'BOB',
                'probability' => 50,
                'commission_rate' => 2.5,
                'assigned_to' => $manager->id,
                'expected_close_date' => Carbon::today()->addDays(15),
            ],
            [
                'title' => 'Implementación ERP',
                'client' => $clients->where('company_name', 'TechBolivia Solutions')->first(),
                'vertical' => $tecnologia,
                'stage_name' => 'Demo',
                'amount' => 120000,
                'currency' => 'BOB',
                'probability' => 30,
                'assigned_to' => $vendedor->id,
                'expected_close_date' => Carbon::today()->addDays(45),
            ],
            [
                'title' => 'Auditoría de Procesos',
                'client' => $clients->where('company_name', 'Constructora Flores Hermanos')->first(),
                'vertical' => $consultoria,
                'stage_name' => 'Negociación',
                'amount' => 28000,
                'currency' => 'BOB',
                'probability' => 75,
                'assigned_to' => $admin->id,
                'expected_close_date' => Carbon::today()->addDays(10),
            ],
            [
                'title' => 'Leasing Equipamiento Industrial',
                'client' => $clients->where('company_name', 'Distribuidora Mamani & Cia')->first(),
                'vertical' => $financiamiento,
                'stage_name' => 'Análisis',
                'amount' => 85000,
                'currency' => 'BOB',
                'probability' => 40,
                'commission_rate' => 1.8,
                'assigned_to' => $manager->id,
                'expected_close_date' => Carbon::today()->addDays(60),
            ],
            [
                'title' => 'Migración Cloud',
                'client' => $clients->where('company_name', 'Industrias Vargas S.R.L.')->first(),
                'vertical' => $tecnologia,
                'stage_name' => 'Contacto',
                'amount' => 65000,
                'currency' => 'BOB',
                'probability' => 20,
                'assigned_to' => $vendedor->id,
                'expected_close_date' => Carbon::today()->addDays(90),
            ],
            [
                'title' => 'Consultoría RRHH',
                'client' => $clients->where('company_name', 'TechBolivia Solutions')->first(),
                'vertical' => $consultoria,
                'stage_name' => 'Ganado',
                'amount' => 18500,
                'currency' => 'BOB',
                'probability' => 100,
                'assigned_to' => $admin->id,
                'actual_close_date' => Carbon::today()->subDays(5),
            ],
            [
                'title' => 'Préstamo Hipotecario Comercial',
                'client' => $clients->where('company_name', 'Constructora Flores Hermanos')->first(),
                'vertical' => $financiamiento,
                'stage_name' => 'Prospecto',
                'amount' => 500000,
                'currency' => 'BOB',
                'probability' => 15,
                'commission_rate' => 1.5,
                'assigned_to' => $manager->id,
                'expected_close_date' => Carbon::today()->addDays(120),
            ],
            [
                'title' => 'Sistema de Seguridad IT',
                'client' => $clients->where('company_name', 'Distribuidora Mamani & Cia')->first(),
                'vertical' => $tecnologia,
                'stage_name' => 'POC',
                'amount' => 42000,
                'currency' => 'USD',
                'probability' => 55,
                'assigned_to' => $vendedor->id,
                'expected_close_date' => Carbon::today()->addDays(25),
            ],
            [
                'title' => 'Rediseño Organizacional',
                'client' => $clients->where('company_name', 'Agro Torrico Exportaciones')->first(),
                'vertical' => $consultoria,
                'stage_name' => 'Calificación',
                'amount' => 35000,
                'currency' => 'BOB',
                'probability' => 35,
                'assigned_to' => $admin->id,
                'expected_close_date' => Carbon::today()->addDays(55),
            ],
        ];

        foreach ($deals as $dealData) {
            $client = $dealData['client'];
            $vertical = $dealData['vertical'];
            $stageName = $dealData['stage_name'];

            if (! $client || ! $vertical) {
                continue;
            }

            $stage = Stage::where('vertical_id', $vertical->id)
                ->where('name', $stageName)
                ->first();

            if (! $stage) {
                continue;
            }

            Deal::firstOrCreate(
                ['title' => $dealData['title'], 'client_id' => $client->id],
                [
                    'client_id' => $client->id,
                    'vertical_id' => $vertical->id,
                    'stage_id' => $stage->id,
                    'assigned_to' => $dealData['assigned_to'],
                    'amount' => $dealData['amount'],
                    'currency' => $dealData['currency'] ?? 'BOB',
                    'probability' => $dealData['probability'],
                    'commission_rate' => $dealData['commission_rate'] ?? null,
                    'expected_close_date' => $dealData['expected_close_date'] ?? null,
                    'actual_close_date' => $dealData['actual_close_date'] ?? null,
                ]
            );
        }
    }
}
