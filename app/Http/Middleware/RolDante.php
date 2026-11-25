<?php

namespace App\Http\Middleware;

class RolDante extends RolBase
{
    protected function roles(): array
    {
        return ['Cuenta-dante'];
    }
}
