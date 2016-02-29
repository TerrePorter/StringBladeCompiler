<?php

namespace Wpb\String_Blade_Compiler;

use Illuminate\View\Engines\EngineInterface;

/**
 * Class View
 *
 * Extension of the Laravel View class.
 */
class View extends \Illuminate\View\View
{
    /**
     * Create a new view instance.
     *
     * @param  \Wpb\String_Blade_Compiler\Factory  $factory
     * @param  \Illuminate\View\Engines\EngineInterface  $engine
     * @param  string|object  $view
     * @param  string  $path
     * @param  array   $data
     *
     */
    public function __construct(Factory $factory, EngineInterface $engine, $view, $path, $data = [])
    {
        parent::__construct($factory, $engine, $view, $path, $data);
    }
}
