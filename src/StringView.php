<?php

namespace Wpb\String_Blade_Compiler;

use Exception;
use Throwable;
use ArrayAccess;
use BadMethodCallException;
use Illuminate\Support\Str;
use Illuminate\Support\MessageBag;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\Support\MessageProvider;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Contracts\View\Engine as Engine;

class StringView extends View
{

    /**
     * Create a new view instance.
     *
     * @param  Factory  $factory
     * @param  Engine  $engine
     * @param  string  $view
     * @param  string  $path
     * @param  array   $data
     *
     */
    public function __construct(Factory $factory, Engine $engine, $view, $path, $data = [])
    {
        $this->view = (is_array($view))?(object) $view:$view;
        $this->path = $path;
        $this->engine = $engine;
        $this->factory = $factory;

        $this->data = $data instanceof Arrayable ? $data->toArray() : (array) $data;

        // check if view has secondsTemplateCacheExpires set, or get from config
        if ( !property_exists($this->view, "secondsTemplateCacheExpires") || !is_numeric($this->view->secondsTemplateCacheExpires) ) {
            $this->view->secondsTemplateCacheExpires = config('blade.secondsTemplateCacheExpires');
            if ( is_null($this->view->secondsTemplateCacheExpires) ) {
                $this->view->secondsTemplateCacheExpires = 0;
            }
        }

        // this is the actually blade template data
        if ( !property_exists($this->view, "template") )
        {
            // is the same as sending a blank template file
            $this->view->template = '';
        }

        // each template requires a unique cache key, or generate one
        if ( !property_exists($this->view, "cache_key") )
        {
            // special, to catch if template is empty
            if (empty($this->view->template)) {
                $this->view->cache_key = md5('_empty_template_');
            } else {
                $this->view->cache_key = md5($this->view->template);
            }
        }
    }

    public function getViewTemplate() {
        return $this->view->template;
    }

    /**
     * Get the name of the view.
     *
     * @return string
     */
    public function getName()
    {
        return (isset($this->view->template)?md5($this->view->template):'StringViewTemplate');
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @return string
     */
    protected function getContents()
    {


        /**
         * This property will be added to models being compiled with StringView
         * to keep track of which field in the model is being compiled
         */
        //$this->path->__string_blade_compiler_template_field = $this->template_field;

        if (is_null($this->path)) {
            return $this->engine->get($this->view, $this->gatherData());
        }

        return $this->engine->get($this->path, $this->gatherData());
        //return parent::getContents();
    }

    /**
     * Checks if a string is a valid timestamp.
     * from https://gist.github.com/sepehr/6351385
     *
     * @param string $timestamp Timestamp to validate.
     *
     * @return bool
     */
    function is_timestamp($timestamp)
    {
        $check = (is_int($timestamp) OR is_float($timestamp))
            ? $timestamp
            : (string) (int) $timestamp;

        return ($check === $timestamp)
            AND ( (int) $timestamp <= PHP_INT_MAX)
            AND ( (int) $timestamp >= ~PHP_INT_MAX);
    }


}