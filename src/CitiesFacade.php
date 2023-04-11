<?php

namespace Vioms\Cities;

use Illuminate\Support\Facades\Facade;

class CitiesFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'cities';
    }
}
