<?php

namespace Wpb\String_Blade_Compiler\Compilers;

use Illuminate\View\Compilers\BladeCompiler as BladeCompilerParent;

class BladeCompiler extends BladeCompilerParent
{

    /**
     *  Switch to force template recompile
     */
    protected $forceTemplateRecompile = false;

    /**
     * Switch to track escape setting for contentTags.
     *
     * @deprecated Just use the escape tags {{{ }}} default
     *
     * @var bool
     */
    // protected $contentTagsEscaped = true;

    /**
     * Array of opening and closing tags for raw echos.
     *
     * @var array
     */
    protected $rawTags = ['{!!', '!!}'];

    /**
     * Array of opening and closing tags for regular echos.
     *
     * @var array
     */
    protected $contentTags = ['{{', '}}'];

    /**
     * Array of opening and closing tags for escaped echos.
     *
     * @var array
     */
    protected $escapedTags = ['{{{', '}}}'];

    /**
     * Sets the content tags used for the compiler.
     *
     * @deprecated This feature was removed from Laravel (https://github.com/laravel/framework/issues/17736)
     *
     * @param  string  $openTag
     * @param  string  $closeTag
     * @param  bool    $escaped
     * @return void
     */
    public function setRawTags($openTag, $closeTag, $escaped = true)
    {
        $this->rawTags = [preg_quote($openTag), preg_quote($closeTag)];
    }

    /**
     * Sets the content tags used for the compiler.
     *
     * @deprecated This feature was removed from Laravel (https://github.com/laravel/framework/issues/17736)
     *
     * @param  string  $openTag
     * @param  string  $closeTag
     * @param  bool    $escaped
     * @return void
     */
    public function setContentTags($openTag, $closeTag, $escaped = true)
    {
        $this->contentTags = [preg_quote($openTag), preg_quote($closeTag)];
    }

    /**
     * Sets the escape tags used for the compiler.
     *
     * @deprecated This feature was removed from Laravel (https://github.com/laravel/framework/issues/17736)
     *
     * @param  string  $openTag
     * @param  string  $closeTag
     * @param  bool    $escaped
     * @return void
     */
    public function setEscapeTags($openTag, $closeTag, $escaped = true)
    {
        $this->escapedTags = [preg_quote($openTag), preg_quote($closeTag)];
    }

    /**
     * Enable/Disable force recompile of templates.
     *
     * @param  bool  $recompile
     * @return void
     */
    public function setForceTemplateRecompile($recompile = true) {
        $this->forceTemplateRecompile = $recompile;
    }

    /**
     * Determine if the view at the given path is expired.
     *
     * @param  string  $path
     * @return bool
     */
    public function isExpired($path)
    {

        // adds ability to force template recompile
        if ($this->forceTemplateRecompile) {
            return true;
        }

        return parent::isExpired($path);
    }

}
