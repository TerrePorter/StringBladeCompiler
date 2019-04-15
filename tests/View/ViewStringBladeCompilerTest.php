<?php

namespace Wpb\String_Blade_Compiler\Tests\View;

use Mockery as m;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Illuminate\Filesystem\Filesystem;
use Wpb\String_Blade_Compiler\Compilers\StringBladeCompiler as BladeCompiler;

class ViewStringBladeCompilerTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testIsExpiredReturnsTrueIfCompiledFileDoesntExist()
    {
        $compiler = new BladeCompiler($files = $this->getFiles(), __DIR__);
        $files->shouldReceive('exists')->once()->with(__DIR__.'/'.sha1('foo').'.php')->andReturn(false);
        $this->assertTrue($compiler->isExpired((object)['cache_key' => 'foo']));
    }

    public function testCannotConstructWithBadCachePath()
    {
        /* StringBladeComplier can have a empty cache path */

        //$this->expectException(InvalidArgumentException::class);
        //$this->expectExceptionMessage('Please provide a valid cache path.');

        //new BladeCompiler($this->getFiles(), null);
        $this->assertTrue(true);
    }

    public function testIsExpiredReturnsTrueWhenModificationTimesWarrant()
    {
        $compiler = new BladeCompiler($files = $this->getFiles(), __DIR__);
        $files->shouldReceive('exists')->once()->with(__DIR__.'/'.sha1('foo').'.php')->andReturn(true);
        //$files->shouldReceive('lastModified')->once()->with('foo')->andReturn(100);
        $files->shouldReceive('lastModified')->once()->with(__DIR__.'/'.sha1('foo').'.php')->andReturn(0);

        $this->assertTrue($compiler->isExpired((object)['cache_key' => 'foo']));
    }

    public function testCompilePathIsProperlyCreated()
    {
        $compiler = new BladeCompiler($this->getFiles(), __DIR__);
        $this->assertEquals(__DIR__.'/'.sha1('foo').'.php', $compiler->getCompiledPath((object)['cache_key' => 'foo']));
    }

    public function testCompileCompilesFileAndReturnsContents()
    {
        $compiler = new BladeCompiler($files = $this->getFiles(), __DIR__);
        //$files->shouldReceive('get')->once()->with('foo')->andReturn('Hello World');
        $files->shouldReceive('put')->once()->with(__DIR__.'/'.sha1('foo').'.php', 'Hello World<?php /**PATH foo ENDPATH**/ ?>');
        $compiler->compile((object)['template' => 'Hello World', 'templateRefKey' => 'foo', 'cache_key' => 'foo']);
    }

    public function testCompileCompilesAndGetThePath()
    {
        $compiler = new BladeCompiler($files = $this->getFiles(), __DIR__);
        //$files->shouldReceive('get')->once()->with('foo')->andReturn('Hello World');
        $files->shouldReceive('put')->once()->with(__DIR__.'/'.sha1('foo').'.php', 'Hello World<?php /**PATH foo ENDPATH**/ ?>');
        $compiler->compile((object)['template' => 'Hello World', 'templateRefKey' => 'foo', 'cache_key' => 'foo']);
        $this->assertEquals('foo', $compiler->getPath());
    }

    public function testCompileSetAndGetThePath()
    {
        $compiler = new BladeCompiler($files = $this->getFiles(), __DIR__);
        $compiler->setPath('foo');
        $this->assertEquals('foo', $compiler->getPath());
    }

    public function testCompileWithPathSetBefore()
    {
        $compiler = new BladeCompiler($files = $this->getFiles(), __DIR__);
        //$files->shouldReceive('get')->once()->with('foo')->andReturn('Hello World');
        $files->shouldReceive('put')->once()->with(__DIR__.'/'.sha1('foo').'.php', 'Hello World<?php /**PATH foo ENDPATH**/ ?>');
        // set path before compilation
        $compiler->setViewData((object)['template' => 'Hello World', 'templateRefKey' => 'foo', 'cache_key' => 'foo']);
        // trigger compilation with $path
        $compiler->compile();
        $this->assertEquals('foo', $compiler->getPath());
    }

    public function testRawTagsCanBeSetToLegacyValues()
    {
        $compiler = new BladeCompiler($this->getFiles(), __DIR__);
        $compiler->setEchoFormat('%s');

        $this->assertEquals('<?php echo e($name); ?>', $compiler->compileString('{{{ $name }}}'));
        $this->assertEquals('<?php echo $name; ?>', $compiler->compileString('{{ $name }}'));
        $this->assertEquals('<?php echo $name; ?>', $compiler->compileString('{{
            $name
        }}'));
    }

    /**
     * @param  string  $content
     * @param  string  $compiled
     *
     * @dataProvider appendViewPathDataProvider
     */
    public function testIncludePathToTemplate($content, $compiled)
    {
        $compiler = new BladeCompiler($files = $this->getFiles(), __DIR__);
        //$files->shouldReceive('get')->once()->with('foo')->andReturn($content);
        $files->shouldReceive('put')->once()->with(__DIR__.'/'.sha1('foo').'.php', $compiled);

        $compiler->compile((object)['template' => $content, 'templateRefKey' => 'foo', 'cache_key' => 'foo']);
    }

    /**
     * @return array
     */
    public function appendViewPathDataProvider()
    {
        return [
            'No PHP blocks' => [
                'Hello World',
                'Hello World<?php /**PATH foo ENDPATH**/ ?>',
            ],
            'Single PHP block without closing ?>' => [
                '<?php echo $path',
                '<?php echo $path ?><?php /**PATH foo ENDPATH**/ ?>',
            ],
            'Ending PHP block.' => [
                'Hello world<?php echo $path ?>',
                'Hello world<?php echo $path ?><?php /**PATH foo ENDPATH**/ ?>',
            ],
            'Ending PHP block without closing ?>' => [
                'Hello world<?php echo $path',
                'Hello world<?php echo $path ?><?php /**PATH foo ENDPATH**/ ?>',
            ],
            'PHP block between content.' => [
                'Hello world<?php echo $path ?>Hi There',
                'Hello world<?php echo $path ?>Hi There<?php /**PATH foo ENDPATH**/ ?>',
            ],
            'Multiple PHP blocks.' => [
                'Hello world<?php echo $path ?>Hi There<?php echo $path ?>Hello Again',
                'Hello world<?php echo $path ?>Hi There<?php echo $path ?>Hello Again<?php /**PATH foo ENDPATH**/ ?>',
            ],
            'Multiple PHP blocks without closing ?>' => [
                'Hello world<?php echo $path ?>Hi There<?php echo $path',
                'Hello world<?php echo $path ?>Hi There<?php echo $path ?><?php /**PATH foo ENDPATH**/ ?>',
            ],
            'Short open echo tag' => [
                'Hello world<?= echo $path',
                'Hello world<?= echo $path ?><?php /**PATH foo ENDPATH**/ ?>',
            ],
            'Echo XML declaration' => [
                '<?php echo \'<?xml version="1.0" encoding="UTF-8"?>\';',
                '<?php echo \'<?xml version="1.0" encoding="UTF-8"?>\'; ?><?php /**PATH foo ENDPATH**/ ?>',
            ],
        ];
    }

    public function testDontIncludeEmptyPath()
    {
        /* Stupid test, you can't get a file without a file name */
        /*
        $compiler = new BladeCompiler($files = $this->getFiles(), __DIR__);
        $files->shouldReceive('get')->once()->with('')->andReturn('Hello World');
        $files->shouldReceive('put')->once()->with(__DIR__.'/'.sha1('').'.php', 'Hello World');
        $compiler->setPath('');
        $compiler->compile();
        */
    }

    public function testDontIncludeNullPath()
    {
        /* Stupid test, you can't get a file named null */
        /*
        $compiler = new BladeCompiler($files = $this->getFiles(), __DIR__);
        $files->shouldReceive('get')->once()->with(null)->andReturn('Hello World');
        $files->shouldReceive('put')->once()->with(__DIR__.'/'.sha1(null).'.php', 'Hello World');
        $compiler->setPath(null);
        $compiler->compile();
        */
    }

    public function testShouldStartFromStrictTypesDeclaration()
    {
        $compiler = new BladeCompiler($files = $this->getFiles(), __DIR__);
        $strictTypeDecl = "<?php\ndeclare(strict_types = 1);";
        $this->assertTrue(substr($compiler->compileString("<?php\ndeclare(strict_types = 1);\nHello World"),
            0, strlen($strictTypeDecl)) === $strictTypeDecl);
    }

    protected function getFiles()
    {
        return m::mock(Filesystem::class);
    }
}
