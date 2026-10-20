<?php

namespace App\Modules\Auth\Facades;

use App\Modules\Auth\Services\LoginService;
use Illuminate\Support\Facades\Facade;

class LoginFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LoginService::class;
    }
}
