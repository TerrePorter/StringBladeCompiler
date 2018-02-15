<?php

namespace Wpb\String_Blade_Compiler\Compilers;

use Config;
use Illuminate\View\Compilers\CompilerInterface;

class StringBladeCompiler extends BladeCompiler implements CompilerInterface {

	/**
	 * Compile the view at the given path.
	 *
	 * @param  object $viewData
	 * @return void
	 */
	public function compile($viewData = null)
	{

        // get the template data
        $string = $viewData->template;

		// Compile to PHP
		$contents = $this->compileString($string);

        // check/save cache
		if ( ! is_null($this->cachePath))
		{
			$this->files->put($this->getCompiledPath($viewData), $contents);

		}
	}

	/**
	 * Get the path to the compiled version of a view.
	 *
	 * @param  object  $viewData
	 * @return string
	 */
	public function getCompiledPath($viewData)
	{
		/*
		 * A unique path for the given model instance must be generated
		 * so the view has a place to cache. The following generates a
		 * path using almost the same logic as Blueprint::createIndexName()
		 */
		return $this->cachePath.'/'.$viewData->cache_key;
	}

	/**
	 * Determine if the view at the given path is expired.
	 *
	 * @param  string  $viewData
	 * @return bool
	 */
	public function isExpired($viewData)
	{

		$compiled = $this->getCompiledPath($viewData);

		// If the compiled file doesn't exist we will indicate that the view is expired
		// so that it can be re-compiled. Else, we will verify the last modification
		// of the views is less than the modification times of the compiled views.
		if ( ! $this->cachePath || ! $this->files->exists($compiled))
		{
			return true;
		}

        // If set to 0, then return cache has expired
        if ($viewData->secondsTemplateCacheExpires==0) {
            return true;
        }

        // Note: The lastModified time for a file on homestead will use the time from the host system.
        //       This means the vm time could be off, so setting the timeout to seconds may not work as expected.

        return time() >= ($this->files->lastModified($compiled) + $viewData->secondsTemplateCacheExpires) ;
	}
}
