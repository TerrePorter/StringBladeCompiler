<?php

namespace Wpb\String_Blade_Compiler\Tests\Blade;

class BladeIncludeIfTest extends AbstractBladeTestCase
{
    public function testIncludeIfsAreCompiled()
    {
        $this->assertEquals('<?php if ($__env->exists(\'foo\')) echo $__env->make(\'foo\', \Illuminate\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\']))->render(); ?>', $this->compiler->compileString('@includeIf(\'foo\')'));
        $this->assertEquals('<?php if ($__env->exists(name(foo))) echo $__env->make(name(foo), \Illuminate\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\']))->render(); ?>', $this->compiler->compileString('@includeIf(name(foo))'));
    }
}
