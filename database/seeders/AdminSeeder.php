<?php

namespace Database\Seeders;

use App\Modules\Usuarios\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /** Crea (o reutiliza) la cuenta de administrador del sistema. */
    public function run(): void
    {
        if (! User::where('email', UserFactory::ADMIN_EMAIL)->exists()) {
            User::factory()->administrador()->create();
        }
    }
}
