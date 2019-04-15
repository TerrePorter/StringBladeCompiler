<?php
namespace Wpb\String_Blade_Compiler;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\View\Factory as FactoryParent;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\ViewFinderInterface;
use InvalidArgumentException;

class Factory extends FactoryParent
{

    /**
     * The engine implementation.
     *
     * @var \Illuminate\View\Engines\EngineResolver
     */
    protected $engines;

    /**
     * The view finder implementation.
     *
     * @var \Illuminate\View\ViewFinderInterface
     */
    protected $finder;

    /**
     * The event dispatcher instance.
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * The IoC container instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * Data that should be available to all templates.
     *
     * @var array
     */
    protected $shared = [];

    /**
     * The extension to engine bindings.
     *
     * @var array
     */
    protected $extensions = [
        'blade.php' => 'blade',
        'php' => 'php',
        'css' => 'file',
    ];

    /**
     * The view composer events.
     *
     * @var array
     */
    protected $composers = [];

    /**
     * The number of active rendering operations.
     *
     * @var int
     */
    protected $renderCount = 0;

    /**
     * Get the evaluated view contents for the given view.
     *
     * @param  string|array  $view
     * @param  array   $data
     * @param  array   $mergeData
     * @return \Illuminate\Contracts\View\View|\Wpb\String_Blade_Compiler\StringView
     */
    public function make($view, $data = [], $mergeData = [])
    {
        $data = array_merge($mergeData, $this->parseData($data));

        // For string rendering
        if (is_array($view)) {
            return tap($this->stringViewInstance($view, $data), function ($view) {
                $this->callCreator($view);
            });
        }

        $path = $this->finder->find(
            $view = $this->normalizeName($view)
        );

        // Next, we will create the view instance and call the view creator for the view
        // which can set any data, etc. Then we will return the view instance back to
        // the caller for rendering or performing other view manipulations on this.

        return tap($this->viewInstance($view, $path, $data), function ($view) {
            $this->callCreator($view);
        });
    }

    /**
     * Create a new string view instance from the given arguments.
     *
     * @param  string|array  $view
     * @param  array  $data
     * @return StringView
     */
    protected function stringViewInstance($view, $data)
    {
        return new StringView($this, $this->engines->resolve('stringblade'), $view, null, $data);
    }

    /**
     * Flush all of the section contents if done rendering.
     *
     * @return void
     */
    public function flushStateIfDoneRendering()
    {
        if ($this->doneRendering()) {
            $this->flushState();
        }
    }

    /**
     * Flush all of the factory state like sections and stacks.
     *
     * @return void
     */
    public function flushState()
    {
        $this->renderCount = 0;

        $this->flushSections();
        $this->flushStacks();
    }

    /**
     * Get the appropriate view engine for the given string key.
     *
     * @param  string  $stringkey
     * @return \Illuminate\Contracts\View\Engine
     *
     *  ['file', 'php', 'blade', 'stringblade'] in StringBladeServiceProvider:registerEngineResolver
     *
     * @throws \InvalidArgumentException
     */
    public function getEngineFromStringKey($stringkey)
    {
        // resolve function throws error if $stringkey is not a registered engine
        return $this->engines->resolve($stringkey);
    }

}
