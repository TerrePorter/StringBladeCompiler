<?php

namespace Wpb\String_Blade_Compiler\Compilers;

class BladeCompiler extends \Illuminate\View\Compilers\BladeCompiler
{

    /**
     *  Switch to force template recompile
     */
    protected $forceTemplateRecompile = false;

    /**
     * Switch to track escape setting for contentTags.
     *
     * @var bool
     */
    protected $contentTagsEscaped = true;

    /**
     * Compile the "regular" echo statements.
     *
     * @param  string  $value
     * @return string
     */
    protected function compileRegularEchos($value)
    {
        $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->contentTags[0], $this->contentTags[1]);

        $callback = function ($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3].$matches[3];

            if ($this->contentTagsEscaped) {
                $wrapped = sprintf('e(%s)', $matches[2]);
            } else {
                $wrapped = sprintf('%s', $matches[2]);
            }

            return $matches[1] ? substr($matches[0], 1) : '<?php echo '.$wrapped.'; ?>'.$whitespace;
        };

        return preg_replace_callback($pattern, $callback, $value);
    }

    /**
     * Compile the escaped echo statements.
     *
     * @param  string  $value
     * @return string
     */
    protected function compileEscapedEchos($value)
    {
        $pattern = sprintf('/%s\s*(.+?)\s*%s(\r?\n)?/s', $this->escapedTags[0], $this->escapedTags[1]);

        $callback = function ($matches) {
            $whitespace = empty($matches[2]) ? '' : $matches[2].$matches[2];

            return '<?php echo e('.$matches[1].'); ?>'.$whitespace;
        };

        return preg_replace_callback($pattern, $callback, $value);
    }

    /**
     * Sets the content tags used for the compiler.
     *
     * @param  string  $openTag
     * @param  string  $closeTag
     * @param  bool    $escaped
     * @return void
     */
    public function setContentTags($openTag, $closeTag, $escaped = true)
    {
        $this->setContentTagsEscaped($escaped);

        $this->contentTags = [preg_quote($openTag), preg_quote($closeTag)];
    }

    /**
     * Sets the escaped content tags used for the compiler.
     *
     * @param  string  $openTag
     * @param  string  $closeTag
     * @return void
     */
    public function setEscapedContentTags($openTag, $closeTag)
    {
        $this->escapedTags = array(preg_quote($openTag), preg_quote($closeTag));
    }

    /**
     * Enable/Disable escape setting for contentTags.
     *
     * @param  bool  $escaped
     * @return void
     */
    public function setContentTagsEscaped($escaped = true) {
        $this->contentTagsEscaped = $escaped;
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

    /**
     * Set the echo format to be used by the compiler.
     *
     * Removed in custom version
     *
     * @deprecated deprecated since version 1.0.0
     * @param  string  $format
     * @return void     *
     */
    public function setEchoFormat($format) {}
}
