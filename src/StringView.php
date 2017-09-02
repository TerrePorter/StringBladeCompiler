<?php

namespace Wpb\String_Blade_Compiler;

use App, ArrayAccess;
use Config;
use Illuminate\Contracts\View\Engine;
use Illuminate\Contracts\View\View as ViewContract;

class StringView extends View implements ArrayAccess, ViewContract {

	protected $template_field = 'template';

    /**
     * Create a new view instance.
     *
     * @param  \Wpb\String_Blade_Compiler\Factory  $factory
     * @param  \Illuminate\Contracts\View\Engine  $engine
     * @param  string  $view
     * @param  string  $path
     * @param  array   $data
     *
     */
    public function __construct(Factory $factory, Engine $engine, $view, $path, $data = [])
    {
        // setup variables
        $this->view = (is_array($view))?(object) $view:$view;
        $this->path = $this->view;
        $this->engine = $engine;
        $this->factory = $factory;

        $this->data = $this->parseData($data);

        //$this->data = $data instanceof Arrayable ? $data->toArray() : (array) $data;

       // if ($data instanceof Arrayable) {
        //    var_dump($this->data);
        //}


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

