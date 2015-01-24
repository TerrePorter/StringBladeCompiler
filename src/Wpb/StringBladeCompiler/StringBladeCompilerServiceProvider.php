<?php namespace Wpb\StringBladeCompiler;

use Illuminate\Support\ServiceProvider;

class StringBladeCompilerServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind('stringview', 'Wpb\StringBladeCompiler\StringView');

                /*
               * This removes the need to add a facade in the config\app
               */
                $this->app->booting(function()
                {
                    $loader = \Illuminate\Foundation\AliasLoader::getInstance();
                    $loader->alias('StringView', 'Wpb\StringBladeCompiler\Facades\StringView');
                });
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
