<?php

declare (strict_types=1);
namespace RectorPrefix20220126\Idiosyncratic\EditorConfig;

use RectorPrefix20220126\Idiosyncratic\EditorConfig\Declaration\Factory;
use ErrorException;
use RectorPrefix20220126\PHPUnit\Framework\TestCase;
class SectionTest extends \RectorPrefix20220126\PHPUnit\Framework\TestCase
{
    public function testGetDeclaration() : void
    {
        $section = new \RectorPrefix20220126\Idiosyncratic\EditorConfig\Section('**/', '*.php', ['indent_size' => '4', 'indent_style' => 'space'], new \RectorPrefix20220126\Idiosyncratic\EditorConfig\Declaration\Factory());
        $this->assertEquals('space', $section->indent_style->getValue());
        $this->assertEquals(4, $section->indent_size->getValue());
        $this->assertFalse(isset($section->tab_width));
    }
    public function testMatchingWindowsPath() : void
    {
        $section = new \RectorPrefix20220126\Idiosyncratic\EditorConfig\Section('**/', '*.php', ['indent_size' => '4', 'indent_style' => 'space'], new \RectorPrefix20220126\Idiosyncratic\EditorConfig\Declaration\Factory());
        $this->assertTrue($section->matches('my\\composer.php'));
    }
    public function testGetMissingDeclaration() : void
    {
        $section = new \RectorPrefix20220126\Idiosyncratic\EditorConfig\Section('**/', '*.php', ['indent_size' => '4', 'indent_style' => 'space'], new \RectorPrefix20220126\Idiosyncratic\EditorConfig\Declaration\Factory());
        $this->expectException(\ErrorException::class);
        $section->tab_width;
    }
}
