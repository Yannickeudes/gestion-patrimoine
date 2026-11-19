<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Compte administrateur par defaut (a changer en production)
        User::firstOrCreate(
            ['email' => 'admin@gpe.local'],
            [
                'name' => 'Administrateur GPE',
                'password' => 'password',
                'role' => User::ROLE_ADMIN,
            ]
        );

        User::firstOrCreate(
            ['email' => 'agent@gpe.local'],
            [
                'name' => 'Agent Patrimoine',
                'password' => 'password',
                'role' => User::ROLE_AGENT,
            ]
        );

        User::firstOrCreate(
            ['email' => 'responsable@gpe.local'],
            [
                'name' => 'Responsable Patrimoine',
                'password' => 'password',
                'role' => User::ROLE_RESPONSABLE,
            ]
        );
    }
}
