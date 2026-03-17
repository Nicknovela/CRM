<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            [
                'name' => 'Roberto Vargas',
                'company_name' => 'Industrias Vargas S.R.L.',
                'email' => 'rvargas@industrias-vargas.bo',
                'phone' => '+591 2 2441234',
                'industry' => 'Manufactura',
                'website' => 'https://industrias-vargas.bo',
                'address' => 'Av. Blanco Galindo Km 5, Cochabamba, Bolivia',
            ],
            [
                'name' => 'María Elena Torrico',
                'company_name' => 'Agro Torrico Exportaciones',
                'email' => 'metorrico@agrotorrico.bo',
                'phone' => '+591 4 4521890',
                'industry' => 'Agricultura',
                'address' => 'Calle Baptista 230, Santa Cruz, Bolivia',
            ],
            [
                'name' => 'Jorge Luis Quispe',
                'company_name' => 'TechBolivia Solutions',
                'email' => 'jquispe@techbolivia.bo',
                'phone' => '+591 76543210',
                'industry' => 'Tecnología',
                'website' => 'https://techbolivia.bo',
                'address' => 'Edificio Multicentro, Piso 8, La Paz, Bolivia',
            ],
            [
                'name' => 'Ana Cecilia Flores',
                'company_name' => 'Constructora Flores Hermanos',
                'email' => 'aflores@constructoraflores.bo',
                'phone' => '+591 2 2789012',
                'industry' => 'Construcción',
                'address' => 'Av. 6 de Agosto 2855, La Paz, Bolivia',
            ],
            [
                'name' => 'Carlos Enrique Mamani',
                'company_name' => 'Distribuidora Mamani & Cia',
                'email' => 'cmamani@distribuidoramm.bo',
                'phone' => '+591 4 4234567',
                'industry' => 'Distribución',
                'address' => 'Av. Roca y Coronado, Sucre, Bolivia',
            ],
        ];

        foreach ($clients as $client) {
            Client::firstOrCreate(['email' => $client['email']], $client);
        }
    }
}
