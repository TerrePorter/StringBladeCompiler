<?php

namespace Wpb\String_Blade_Compiler;

use Illuminate\View\Engines\EngineResolver;
use Wpb\String_Blade_Compiler\Engines\CompilerEngine;
use Wpb\String_Blade_Compiler\Compilers\BladeCompiler;
use Wpb\String_Blade_Compiler\Compilers\StringBladeCompiler;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\App;

/**
 * Class ViewServiceProvider
 *
 * Service providers are the central place of all Laravel application bootstrapping.
 * Your own application, as well as all of Laravel's core services are bootstrapped
 * via service providers.
 *
 * ### Functionality
 *
 * * Merge in the config.
 * * Set up an alias StringBlade to the Facace that we provide.
 * * Register the resolvers and other components that we need.
 * * Boot the TwigBridge\ServiceProvider to save the caller having to do that.
 *
 * @see  Illuminate\Support\ServiceProvider
 * @link http://laravel.com/docs/5.1/providers
 */
class ViewServiceProvider extends \Illuminate\View\ViewServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        // include the package config
        $this->mergeConfigFrom(
            __DIR__.'/config/blade.php', 'blade'
        );

        $this->app->booting(function () {
            $loader = AliasLoader::getInstance();
            $loader->alias('StringBlade', 'Wpb\String_Blade_Compiler\Facades\StringBlade');
        });

        // continue with parent register
        parent::register();
    }

    /**
     * Register the engine resolver instance.
     *
     * @return void
     */
    public function registerEngineResolver()
    {
        $this->app->singleton('view.engine.resolver', function () {
            $resolver = new EngineResolver;

            // Next we will register the various engines with the resolver so that the
            // environment can resolve the engines it needs for various views based
            // on the extension of view files. We call a method for each engines.
            foreach (['php', 'blade', 'StringBlade'] as $engine) {
                $this->{'register'.ucfirst($engine).'Engine'}($resolver);
            }

            return $resolver;
        });
    }

    /**
     * Register the StringBlade engine implementation.
     *
     * @param  \Illuminate\View\Engines\EngineResolver  $resolver
     * @return void
     */
    public function registerStringBladeEngine($resolver)
    {
        $app = $this->app;

        // The Compiler engine requires an instance of the CompilerInterface, which in
        // this case will be the Blade compiler, so we'll first create the compiler
        // instance to pass into the engine so it can compile the views properly.
        $app->singleton('stringblade.compiler', function ($app) {
            $cache = $app['config']['view.compiled'];
            return new StringBladeCompiler($app['files'], $cache, $app['twig']);
        });

        $resolver->register('stringblade', function () use ($app) {
            return new CompilerEngine($app['stringblade.compiler'], $app['files']);
        });
    }

    /**
     * Perform post-registration booting of services.
     *
     */
    public function boot()
    {
        // setup publishing of config
        $this->publishes([
            __DIR__.'/config/blade.php' => config_path('blade.php'),
        ], 'config');

        // Register other providers required by this provider, which saves the caller
        // from having to register them each individually.
        App::register(\TwigBridge\ServiceProvider::class);
    }

    /**
     * Register the Blade engine implementation.
     *
     * @param  \Illuminate\View\Engines\EngineResolver  $resolver
     * @return void
     */
    public function registerBladeEngine($resolver)
    {
        $app = $this->app;

        // The Compiler engine requires an instance of the CompilerInterface, which in
        // this case will be the Blade compiler, so we'll first create the compiler
        // instance to pass into the engine so it can compile the views properly.
        $app->singleton('blade.compiler', function ($app) {
            $cache = $app['config']['view.compiled'];
            return new BladeCompiler($app['files'], $cache);
        });

        $resolver->register('blade', function () use ($app) {
            return new CompilerEngine($app['blade.compiler'], $app['files']);
        });
    }

    /**
     * Register the view environment.
     *
     * @return void
     */
    public function registerFactory()
    {
        $this->app->singleton('view', function ($app) {
            // Next we need to grab the engine resolver instance that will be used by the
            // environment. The resolver will be used by an environment to get each of
            // the various engine implementations such as plain PHP or Blade engine.
            $resolver = $app['view.engine.resolver'];

            $finder = $app['view.finder'];

            $env = new Factory($resolver, $finder, $app['events']);

            // We will also set the container instance on this view environment since the
            // view composers may be classes registered in the container, which allows
            // for great testable, flexible composers for the application developer.
            $env->setContainer($app);

            $env->share('app', $app);

            return $env;
        });
    }
}
