<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Idiosyncratic\EditorConfig\Declaration;

use RectorPrefix20210510\Idiosyncratic\EditorConfig\Exception\InvalidValue;
use RectorPrefix20210510\PHPUnit\Framework\TestCase;
use RuntimeException;
class IndentStyleTest extends TestCase
{
    public function testValidValues()
    {
        $declaration = new IndentStyle('tab');
        $this->assertEquals('indent_style', $declaration->getName());
        $this->assertEquals('tab', $declaration->getValue());
        $declaration = new IndentStyle('space');
        $this->assertEquals('indent_style', $declaration->getName());
        $this->assertEquals('space', $declaration->getValue());
    }
    public function testInvalidValues()
    {
        $this->expectException(InvalidValue::class);
        $declaration = new IndentStyle('true');
        $this->expectException(InvalidValue::class);
        $declaration = new IndentStyle('spaces');
    }
}
