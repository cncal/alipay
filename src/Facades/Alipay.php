<?php

namespace Cncal\Alipay\Facades;

use Illuminate\Support\Facades\Facade;

class Alipay extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'alipay';
    }
}