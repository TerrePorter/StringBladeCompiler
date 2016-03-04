<?php

namespace Wpb\String_Blade_Compiler;

use ArrayAccess;
use Illuminate\View\Engines\EngineInterface;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Fluent;

/**
 * Class StringView
 *
 * Extension to the Laravel View class which can store strings as views.
 * Mostly relates to storing the various components of the view object
 * such as template, cache_key, secondsTemplateCacheExpires.
 */
class StringView extends View implements ArrayAccess, ViewContract
{

    /**
     * The name of the view, or an object representing the view.
     *
     * Object attributes:
     *
     * * template
     * * cache_key
     * * secondsTemplateCacheExpires
     *
     * @var string|Fluent
     */
    protected $view;

    /** @var string for use in models (not sure if still relevant) */
    protected $template_field = 'template';

    /**
     * Create a new view instance.
     *
     * @param  \Wpb\String_Blade_Compiler\Factory  $factory
     * @param  \Illuminate\View\Engines\EngineInterface  $engine
     * @param  string|array|Arrayable|Fluent  $view
     * @param  string  $path
     * @param  array   $data
     *
     */
    public function __construct(Factory $factory, EngineInterface $engine, $view, $path, $data = [])
    {
        // setup variables
        if (is_array($view)) {
            $view = new Fluent($view);
        } elseif ($view instanceof Arrayable) {
            $view = new Fluent($view);
        } elseif (is_string($view)) {
            $view = new Fluent(['template' => $view]);
        }

        parent::__construct($factory, $engine, $view, $view, $data);

        // check if view has secondsTemplateCacheExpires set, or get from config
        if (! isset($this->view->secondsTemplateCacheExpires) ||
            ! is_numeric($this->view->secondsTemplateCacheExpires)) {
            $this->view->secondsTemplateCacheExpires = config('blade.secondsTemplateCacheExpires');
            if (is_null($this->view->secondsTemplateCacheExpires)) {
                $this->view->secondsTemplateCacheExpires = 0;
            }
        }

        // this is the actually blade template data
        if (empty($this->view->template)) {
            // is the same as sending a blank template file
            $this->view->template = '';
        }

        // each template requires a unique cache key, or generate one
        // special, to catch if template is empty
        if (empty($this->view->template)) {
            $this->view->cache_key = md5('_empty_template_');
        } else {
            $this->view->cache_key = md5($this->view->template);
        }
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
     * Get a evaluated view contents for the given view.
     *
     * @param  object  $view
     * @param  array   $data
     * @param  array   $mergeData
     * @return \Illuminate\View\View
     * @throws \Exception
     */
    public function make($view, $data = array(), $mergeData = array())
    {
        $this->path = $view;
        $this->data = array_merge($mergeData, $this->parseData($data));
        return $this;
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
        $this->path->__string_blade_compiler_template_field = $this->template_field;

        return $this->engine->get($this->path, $this->gatherData());
        //return parent::getContents();
    }

    /**
     * Parse the given data into a raw array.
     *
     * @param  mixed  $data
     * @return array
     */
    protected function parseData($data)
    {
        return $data instanceof Arrayable ? $data->toArray() : $data;
    }

   /**
    * Checks if a string is a valid timestamp.
    * from https://gist.github.com/sepehr/6351385
    *
    * @param string $timestamp Timestamp to validate.
    *
    * @return bool
    */
    public function is_timestamp($timestamp)
    {
        $check = (is_int($timestamp) or is_float($timestamp))
        ? $timestamp
        : (string) (int) $timestamp;

        return ($check === $timestamp)
        and ((int) $timestamp <= PHP_INT_MAX)
        and ((int) $timestamp >= ~PHP_INT_MAX);
    }
}
