<?php

namespace Illuminate\Wpb\String_Blade_Compiler\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see Illuminate\Wpb\String_Blade_Compiler\Compilers\StringBladeCompiler
 */
class StringBlade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return static::$app['view']->getEngineResolver()->resolve('stringblade')->getCompiler();
    }
}
