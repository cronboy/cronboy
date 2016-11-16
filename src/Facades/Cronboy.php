<?php

namespace Cronboy\Cronboy\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Cronboy.
 */
class Cronboy extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Cronboy\Cronboy\Cronboy::class;
    }
}
