<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@crm.test'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'is_active' => true,
                'timezone' => 'America/La_Paz',
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        $manager = User::firstOrCreate(
            ['email' => 'gerente@crm.test'],
            [
                'name' => 'Gerente Comercial',
                'password' => Hash::make('password'),
                'is_active' => true,
                'timezone' => 'America/La_Paz',
                'email_verified_at' => now(),
            ]
        );
        $manager->assignRole('manager');

        $vendedor = User::firstOrCreate(
            ['email' => 'vendedor@crm.test'],
            [
                'name' => 'Carlos Méndez',
                'password' => Hash::make('password'),
                'is_active' => true,
                'timezone' => 'America/La_Paz',
                'email_verified_at' => now(),
            ]
        );
        $vendedor->assignRole('vendedor');
    }
}
