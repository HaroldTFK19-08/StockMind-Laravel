<?php

namespace App\Http\Middleware;

class RolInstructor extends RolBase
{
    protected function roles(): array
    {
        return ['Instructor'];
    }
}
