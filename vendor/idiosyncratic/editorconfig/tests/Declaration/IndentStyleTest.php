<?php

declare (strict_types=1);
namespace RectorPrefix20220219\Idiosyncratic\EditorConfig\Declaration;

use RectorPrefix20220219\Idiosyncratic\EditorConfig\Exception\InvalidValue;
use RectorPrefix20220219\PHPUnit\Framework\TestCase;
use RuntimeException;
class IndentStyleTest extends \RectorPrefix20220219\PHPUnit\Framework\TestCase
{
    public function testValidValues()
    {
        $declaration = new \RectorPrefix20220219\Idiosyncratic\EditorConfig\Declaration\IndentStyle('tab');
        $this->assertEquals('indent_style', $declaration->getName());
        $this->assertEquals('tab', $declaration->getValue());
        $declaration = new \RectorPrefix20220219\Idiosyncratic\EditorConfig\Declaration\IndentStyle('space');
        $this->assertEquals('indent_style', $declaration->getName());
        $this->assertEquals('space', $declaration->getValue());
    }
    public function testInvalidValues()
    {
        $this->expectException(\RectorPrefix20220219\Idiosyncratic\EditorConfig\Exception\InvalidValue::class);
        $declaration = new \RectorPrefix20220219\Idiosyncratic\EditorConfig\Declaration\IndentStyle('true');
        $this->expectException(\RectorPrefix20220219\Idiosyncratic\EditorConfig\Exception\InvalidValue::class);
        $declaration = new \RectorPrefix20220219\Idiosyncratic\EditorConfig\Declaration\IndentStyle('spaces');
    }
}
