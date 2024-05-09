<?php

namespace App\Facade;

use Illuminate\Support\Facades\Facade;

class RecaptchaFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'recaptcha';
    }
}
