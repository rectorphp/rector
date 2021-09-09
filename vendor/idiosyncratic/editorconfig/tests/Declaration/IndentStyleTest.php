<?php

declare (strict_types=1);
namespace RectorPrefix20210909\Idiosyncratic\EditorConfig\Declaration;

use RectorPrefix20210909\Idiosyncratic\EditorConfig\Exception\InvalidValue;
use RectorPrefix20210909\PHPUnit\Framework\TestCase;
use RuntimeException;
class IndentStyleTest extends \RectorPrefix20210909\PHPUnit\Framework\TestCase
{
    public function testValidValues()
    {
        $declaration = new \RectorPrefix20210909\Idiosyncratic\EditorConfig\Declaration\IndentStyle('tab');
        $this->assertEquals('indent_style', $declaration->getName());
        $this->assertEquals('tab', $declaration->getValue());
        $declaration = new \RectorPrefix20210909\Idiosyncratic\EditorConfig\Declaration\IndentStyle('space');
        $this->assertEquals('indent_style', $declaration->getName());
        $this->assertEquals('space', $declaration->getValue());
    }
    public function testInvalidValues()
    {
        $this->expectException(\RectorPrefix20210909\Idiosyncratic\EditorConfig\Exception\InvalidValue::class);
        $declaration = new \RectorPrefix20210909\Idiosyncratic\EditorConfig\Declaration\IndentStyle('true');
        $this->expectException(\RectorPrefix20210909\Idiosyncratic\EditorConfig\Exception\InvalidValue::class);
        $declaration = new \RectorPrefix20210909\Idiosyncratic\EditorConfig\Declaration\IndentStyle('spaces');
    }
}
