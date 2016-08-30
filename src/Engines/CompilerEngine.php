<?php

namespace Wpb\String_Blade_Compiler\Engines;

use Exception;
use ErrorException;
use Illuminate\View\Compilers\CompilerInterface;

class CompilerEngine extends \Illuminate\View\Engines\CompilerEngine
{
    /**
     * Bool Flag to Enable/Disable removal of the generated view cache file.
     *
     * @var bool
     */
    public $deleteViewCacheAfterRender = false;

    /**
     * Get the evaluated contents of the view.
     *
     * @param  string  $path
     * @param  array   $data
     * @return string
     */
    public function get($path, array $data = [])
    {
        if (!is_object($path)) {
            $this->lastCompiled[] = $path;
        } else {
            $this->lastCompiled[] = 'StringView-'.$path->cache_key;
        }

        // If this given view has expired, which means it has simply been edited since
        // it was last compiled, we will re-compile the views so we can evaluate a
        // fresh copy of the view. We'll pass the compiler the path of the view.
        if ($this->compiler->isExpired($path)) {
            $this->compiler->compile($path);
        }

        $compiled = $this->compiler->getCompiledPath($path);

        // Once we have the path to the compiled file, we will evaluate the paths with
        // typical PHP just like any other templates. We also keep a stack of views
        // which have been rendered for right exception messages to be generated.
        $results = $this->evaluatePath($compiled, $data);



        array_pop($this->lastCompiled);

        return $results;
    }

    /**
     * Set the delete view cache after render flag.
     *
     * @param bool $delete
     */
    public function setDeleteViewCacheAfterRender($delete = true) {
        $this->deleteViewCacheAfterRender = $delete;
    }
}
