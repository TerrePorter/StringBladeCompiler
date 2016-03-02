<?php

namespace Wpb\String_Blade_Compiler\Compilers;

use Illuminate\View\Compilers\CompilerInterface;
use Twig_Environment;
use Illuminate\Filesystem\Filesystem;

class StringBladeCompiler extends BladeCompiler implements CompilerInterface
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /** @var  array */
    protected $loadedTemplates;

    /**
     * Create a new compiler instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  string  $cachePath
     * @param  \Twig_Environment $twig
     */
    public function __construct(Filesystem $files, $cachePath, Twig_Environment $twig)
    {
        parent::__construct($files, $cachePath);
        $this->twig = $twig;
    }

    /**
     * Compile the view at the given path.
     *
     * @param  object $viewData
     * @return void
     */
    public function compile($viewData)
    {

        // get the template data
        $string = $viewData->template;

        // Compile to PHP
        switch ($viewData->pagetype) {
            case 'blade':
                $contents = $this->compileString($string);
                break;

            case 'twig':
                // Compiling a twig template creates a file containing the definition
                // for a class of name $cls.
                $cls = $this->twig->getTemplateClass($string);

                // Check to see if the internal cache of the output is available.
                if (isset($this->loadedTemplates[$cls])) {
                    $contents = $this->loadedTemplates[$cls];
                    break;
                }

                // Only compile if we have not already compiled.  If we have not
                // already compiled, then compile and evaluate because that will
                // create the class $cls.
                if (! class_exists($cls, false)) {
                    $twig_content = $this->twig->compileSource($string);
                    eval('?>' . $twig_content);
                }

                // Internally cache the contents
                $this->loadedTemplates[$cls] = new $cls($this->twig);
                $contents = $this->loadedTemplates[$cls];

                break;

            default:
                $contents = $this->compileString($string);
                break;
        }

        // check/save cache
        if (! is_null($this->cachePath)) {
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
     * @param  object  $viewData
     * @return bool
     */
    public function isExpired($viewData)
    {
        $compiled = $this->getCompiledPath($viewData);

        // If the compiled file doesn't exist we will indicate that the view is expired
        // so that it can be re-compiled. Else, we will verify the last modification
        // of the views is less than the modification times of the compiled views.
        if (! $this->cachePath || ! $this->files->exists($compiled)) {
            return true;
        }

        // If set to 0, then return cache has expired
        if ($viewData->secondsTemplateCacheExpires == 0) {
            return true;
        }

        // Note: The lastModified time for a file on homestead will use the time from the host system.
        //       This means the vm time could be off, so setting the timeout to seconds may not work as expected.

        return time() >= ($this->files->lastModified($compiled) + $viewData->secondsTemplateCacheExpires);
    }
}
