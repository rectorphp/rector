<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Idiosyncratic\EditorConfig;

use RectorPrefix20210510\Idiosyncratic\EditorConfig\Declaration\Factory;
use ErrorException;
use RectorPrefix20210510\PHPUnit\Framework\TestCase;
class SectionTest extends TestCase
{
    public function testGetDeclaration() : void
    {
        $section = new Section('**/', '*.php', ['indent_size' => '4', 'indent_style' => 'space'], new Factory());
        $this->assertEquals('space', $section->indent_style->getValue());
        $this->assertEquals(4, $section->indent_size->getValue());
        $this->assertFalse(isset($section->tab_width));
    }
    public function testGetMissingDeclaration() : void
    {
        $section = new Section('**/', '*.php', ['indent_size' => '4', 'indent_style' => 'space'], new Factory());
        $this->expectException(ErrorException::class);
        $section->tab_width;
    }
}
