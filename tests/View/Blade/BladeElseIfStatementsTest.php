<?php

namespace Wpb\String_Blade_Compiler\Tests\Blade;

class BladeElseIfStatementsTest extends AbstractBladeTestCase
{
    public function testElseIfStatementsAreCompiled()
    {
        $string = '@if(name(foo(bar)))
breeze
@elseif(boom(breeze))
boom
@endif';
        $expected = '<?php if(name(foo(bar))): ?>
breeze
<?php elseif(boom(breeze)): ?>
boom
<?php endif; ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }
}
