<?php

namespace App\Modules\Auth\Facades;

use Illuminate\Support\Facades\Facade;
use App\Modules\Auth\Services\LoginService;

class RegisterFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LoginService::class;
    }
}
