<?php

namespace Database\Factories;

use App\Modules\Roles\Models\Rol;
use App\Modules\Shared\Enums\EstadoUsuario;
use App\Modules\Usuarios\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public const ADMIN_EMAIL = 'admin@stockmind.test';

    public const ADMIN_PASSWORD = 'admin123';

    /** Contraseña reutilizable para las pruebas. */
    protected static ?string $password;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'foto_perfil' => null,
            'foto_perfil_public_id' => null,
            'nombre_1' => fake()->firstName(),
            'nombre_2' => fake()->optional()->firstName(),
            'apellido_1' => fake()->lastName(),
            'apellido_2' => fake()->optional()->lastName(),
            'tipo_identidad' => 'CC',
            'numero_identidad' => (string) fake()->unique()->numerify('##########'),
            'telefono' => fake()->unique()->numerify('3#########'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'estado' => EstadoUsuario::Activo->value,
            'remember_token' => Str::random(10),
        ];
    }

    /** Datos fijos y rol de administrador para el seeder. */
    public function administrador(): static
    {
        return $this->state(fn () => [
            'nombre_1' => 'Admin',
            'apellido_1' => 'Sistema',
            'tipo_identidad' => 'CC',
            'numero_identidad' => '9000000001',
            'telefono' => '3000000001',
            'email' => self::ADMIN_EMAIL,
            'password' => Hash::make(self::ADMIN_PASSWORD),
            'estado' => EstadoUsuario::Activo->value,
        ])->afterCreating(function (User $admin): void {
            $rol = Rol::firstOrCreate(
                ['rol' => 'Administrador'],
                ['descripcion' => 'Administrador del sistema StockMind']
            );

            $admin->roles()->syncWithoutDetaching([$rol->id]);
        });
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
