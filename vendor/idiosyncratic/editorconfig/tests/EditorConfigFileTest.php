<?php

declare (strict_types=1);
namespace RectorPrefix20220501\Idiosyncratic\EditorConfig;

use RectorPrefix20220501\Idiosyncratic\EditorConfig\Exception\InvalidValue;
use RectorPrefix20220501\PHPUnit\Framework\TestCase;
use RuntimeException;
class EditorConfigFileTest extends \RectorPrefix20220501\PHPUnit\Framework\TestCase
{
    public function testParseEditorConfigFile() : void
    {
        $path = __DIR__ . '/data/editorconfig';
        $file = new \RectorPrefix20220501\Idiosyncratic\EditorConfig\EditorConfigFile($path);
        $this->assertInstanceOf(\RectorPrefix20220501\Idiosyncratic\EditorConfig\EditorConfigFile::class, $file);
        $this->assertFalse($file->isRoot());
        $this->assertEquals($path, $file->getPath());
    }
    public function testGetPath() : void
    {
        $path = __DIR__ . '/data/editorconfig';
        $file = new \RectorPrefix20220501\Idiosyncratic\EditorConfig\EditorConfigFile($path);
        $this->assertEquals($path, $file->getPath());
    }
    public function testEmptyFile() : void
    {
        $path = __DIR__ . '/data/empty_editorconfig';
        $file = new \RectorPrefix20220501\Idiosyncratic\EditorConfig\EditorConfigFile($path);
        $this->assertEquals('', \trim((string) $file));
    }
    public function testRootFile() : void
    {
        $path = __DIR__ . '/data/root_editorconfig';
        $file = new \RectorPrefix20220501\Idiosyncratic\EditorConfig\EditorConfigFile($path);
        $this->assertTrue($file->isRoot());
        $this->assertTrue(\strpos((string) $file, 'root=true') === 0);
    }
    public function testInvalidRootValue() : void
    {
        $path = __DIR__ . '/data/invalid_root_editorconfig';
        $this->expectException(\RectorPrefix20220501\Idiosyncratic\EditorConfig\Exception\InvalidValue::class);
        $file = new \RectorPrefix20220501\Idiosyncratic\EditorConfig\EditorConfigFile($path);
    }
    public function testFileDoesNotExist() : void
    {
        $this->expectException(\RuntimeException::class);
        $file = new \RectorPrefix20220501\Idiosyncratic\EditorConfig\EditorConfigFile(__DIR__);
    }
    public function testEmptyIndentSize() : void
    {
        $path = __DIR__ . '/data/editorconfig';
        $file = new \RectorPrefix20220501\Idiosyncratic\EditorConfig\EditorConfigFile($path);
        $config = $file->getConfigForPath(__DIR__);
        $this->assertFalse(isset($config['indent_size']));
    }
    /**
     * @dataProvider configForPath
     */
    public function testGetConfigForPath(string $pathToFile, int $expectedIndentSize) : void
    {
        $path = __DIR__ . '/data/editorconfig';
        $file = new \RectorPrefix20220501\Idiosyncratic\EditorConfig\EditorConfigFile($path);
        $config = $file->getConfigForPath($pathToFile);
        $this->assertEquals($expectedIndentSize, $config['indent_size']->getValue());
    }
    public function configForPath() : array
    {
        return ['This .php file has an indentation of 4' => [__FILE__, 4], 'The test.json file has an indentation of 2' => [__DIR__ . '/data/test.json', 2], 'The test.yml has an indentation of 98' => [__DIR__ . '/data/test.yml', 98], 'The test.js has an indentation of 27' => [__DIR__ . '/data/lib/test.js', 27]];
    }
}
