<?php
namespace Wpb\String_Blade_Compiler;

use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\FileViewFinder;
use Illuminate\View\ViewServiceProvider;
use Wpb\String_Blade_Compiler\Compilers\StringBladeCompiler;
use Wpb\String_Blade_Compiler\Engines\CompilerEngine;

class StringBladeServiceProvider extends ViewServiceProvider{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        // include the package config
        $this->mergeConfigFrom(
            __DIR__.'/../config/blade.php', 'blade'
        );

        // load the alias (handled by the Laravel autoloader)
        //$this->app->alias('StringBlade', 'Wpb\String_Blade_Compiler\Facades\StringBlade');

        $this->registerEngineResolver();

        $this->registerViewFinder();

        $this->registerFactory();
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

            $factory = $this->createFactory($resolver, $finder, $app['events']);

            // We will also set the container instance on this view environment since the
            // view composers may be classes registered in the container, which allows
            // for great testable, flexible composers for the application developer.
            $factory->setContainer($app);

            $factory->share('app', $app);

            return $factory;
        });
    }

    /**
     * Create a new Factory Instance.
     *
     * @param  \Illuminate\View\Engines\EngineResolver  $resolver
     * @param  \Illuminate\View\ViewFinderInterface  $finder
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return \Illuminate\View\Factory
     */
    protected function createFactory($resolver, $finder, $events)
    {
        return new Factory($resolver, $finder, $events);
    }

    /**
     * Register the view finder implementation.
     *
     * @return void
     */
    public function registerViewFinder()
    {
        // since view.find should be registered, lets get the paths and hints - in case they have changed
        $oldFinder = [];
        if ($this->app->resolved('view.finder')) {
            $oldFinder['paths'] = $this->app['view']->getFinder()->getPaths();
            $oldFinder['hints'] = $this->app['view']->getFinder()->getHints();
        }

        // recreate the view.finder
        $this->app->bind('view.finder', function ($app) use ($oldFinder) {

            $paths = (isset($oldFinder['paths']))?array_unique(array_merge($app['config']['view.paths'], $oldFinder['paths']), SORT_REGULAR):$app['config']['view.paths'];

            $viewFinder = new FileViewFinder($app['files'], $paths);

            if (!empty($oldFinder['hints'])) {
                array_walk($oldFinder['hints'], function($value, $key) use ($viewFinder) {
                    $viewFinder->addNamespace($key, $value);
                });
            }

            return $viewFinder;
        });
    }

    /**
     * Register the engine resolver instance.
     *
     * @return void
     */
    public function registerEngineResolver()
    {
        // recreate the resolver, adding stringblade
        $this->app->singleton('view.engine.resolver', function () {
            $resolver = new EngineResolver;

            // Next, we will register the various view engines with the resolver so that the
            // environment will resolve the engines needed for various views based on the
            // extension of view file. We call a method for each of the view's engines.
            foreach (['file', 'php', 'blade', 'stringblade'] as $engine) {
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
            return new StringBladeCompiler($app['files'], $cache);
        });

        $resolver->register('stringblade', function () use ($app) {
            return new CompilerEngine($app['stringblade.compiler']);
        });
    }
}