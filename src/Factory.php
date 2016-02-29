<?php

namespace Wpb\String_Blade_Compiler;

use InvalidArgumentException;
use Illuminate\View\Factory as BaseFactory;

/**
 * Class Factory
 *
 * Updated View\Factory class for StringBladeCompiler.
 */
class Factory extends BaseFactory
{
    /**
     * Get the evaluated view contents for the given view.
     *
     * @param  string  $view
     * @param  array   $data
     * @param  array   $mergeData
     * @return \Illuminate\Contracts\View\View
     */
    public function make($view, $data = [], $mergeData = [])
    {
        if (is_array($view)) {

            //$cache =  config('view.compiled');
            //$compiler = new StringBladeCompiler(app('files'), $cache);
            //$engine = new CompilerEngine($compiler);

            $engine = $this->engines->resolve('stringblade');

            $data = array_merge($mergeData, $this->parseData($data));

            $this->callCreator($view = new StringView($this, $engine, $view, 'not-used', $data));
        } else {
            $view = parent::make($view, $data, $mergeData);
        }
        return $view;
    }

    /**
     * Get the appropriate view engine for the given string key.
     *
     * @param  string  $stringkey
     * @return \Illuminate\View\Engines\EngineInterface
     *
     * @throws \InvalidArgumentException
     */
    public function getEngineFromStringKey($stringkey)
    {
        return $this->engines->resolve($stringkey);
    }
}
