<?php

namespace Goldnead\StatamicComponent\Facades;

use Goldnead\StatamicComponent\ComponentRepository;
use Illuminate\Support\Facades\Facade;

class Component extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ComponentRepository::class;
    }
}
