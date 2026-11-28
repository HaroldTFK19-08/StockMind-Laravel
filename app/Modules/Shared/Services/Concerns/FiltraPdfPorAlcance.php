<?php

namespace App\Modules\Shared\Services\Concerns;

use App\Modules\Usuarios\Models\Apprentice;
use App\Modules\Usuarios\Models\Danteaccount;
use App\Modules\Usuarios\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait FiltraPdfPorAlcance
{
    /** @return array<int, int> */
    protected function ambientesAsignadosIds(User $usuario): array
    {
        return Danteaccount::query()
            ->where('user_id', $usuario->id)
            ->whereNull('fecha_fin')
            ->pluck('environment_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /** @return array<int, int> */
    protected function aprendicesDeInstructorIds(?User $usuario): array
    {
        $instructorId = $usuario?->teacher?->id;
        if ($instructorId === null) {
            return [];
        }

        return Apprentice::query()
            ->whereHas('coursegroup', fn (Builder $q) => $q->where('teacher_id', $instructorId))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    protected function restringirConsultaSinAlcance(Builder $query): void
    {
        $query->whereKey([]);
    }
}
