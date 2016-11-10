<?php
/**
 * Created by PhpStorm.
 * User: vitsw
 * Date: 10/17/16
 * Time: 12:17 AM.
 */
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
