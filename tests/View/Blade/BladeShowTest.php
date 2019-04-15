<?php

namespace Wpb\String_Blade_Compiler\Tests\Blade;

class BladeShowTest extends AbstractBladeTestCase
{
    public function testShowsAreCompiled()
    {
        $this->assertEquals('<?php echo $__env->yieldSection(); ?>', $this->compiler->compileString('@show'));
    }
}
