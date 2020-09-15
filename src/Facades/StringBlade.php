<?php

namespace Wpb\String_Blade_Compiler\Facades;

use Illuminate\Support\Facades\Facade;
use Wpb\String_Blade_Compiler\Compilers\StringBladeCompiler;

/**
 * @method static bool exists(string $view)
 * @method static \Illuminate\Contracts\View\View file(string $path, array $data = [], array $mergeData = [])
 * @method static \Illuminate\Contracts\View\View make(string $view, array $data = [], array $mergeData = [])
 * @method static mixed share(array|string $key, $value = null)
 * @method static array composer(array|string $views, \Closure|string $callback)
 * @method static array creator(array|string $views, \Closure|string $callback)
 * @method static \Illuminate\Contracts\View\Factory addNamespace(string $namespace, string|array $hints)
 * @method static \Illuminate\Contracts\View\Factory replaceNamespace(string $namespace, string|array $hints)
 *
 * @see StringBladeCompiler
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
        return app('view')->getEngineResolver()->resolve('stringblade')->getCompiler();
        //return static::$app['view']->getEngineResolver()->resolve('stringblade')->getCompiler();
    }
}
