<?php

namespace Wpb\String_Blade_Compiler;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\View\Engines\EngineInterface;

class View extends \Illuminate\View\View
{

    /**
     * Create a new view instance.
     *
     * @param  \Wpb\String_Blade_Compiler\Factory  $factory
     * @param  \Illuminate\View\Engines\EngineInterface  $engine
     * @param  string  $view
     * @param  string  $path
     * @param  array   $data
     *
     */
    public function __construct(Factory $factory, EngineInterface $engine, $view, $path, $data = [])
    {
        $this->view = $view;
        $this->path = $path;
        $this->engine = $engine;
        $this->factory = $factory;

        $this->data = $data instanceof Arrayable ? $data->toArray() : (array) $data;
    }
}
