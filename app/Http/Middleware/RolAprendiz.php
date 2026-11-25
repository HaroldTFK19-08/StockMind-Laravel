<?php

namespace App\Http\Middleware;

class RolAprendiz extends RolBase
{
    protected function roles(): array
    {
        return ['Aprendiz'];
    }
}
