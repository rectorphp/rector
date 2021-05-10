<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Idiosyncratic\EditorConfig\Declaration;

use RectorPrefix20210510\Idiosyncratic\EditorConfig\Exception\InvalidValue;
use RectorPrefix20210510\PHPUnit\Framework\TestCase;
use RuntimeException;
class IndentSizeTest extends TestCase
{
    public function testValidValues()
    {
        $declaration = new IndentSize('tab');
        $this->assertEquals('indent_size=tab', (string) $declaration);
        $declaration = new IndentSize('4');
        $this->assertEquals('indent_size=4', (string) $declaration);
        $this->assertSame(4, $declaration->getValue());
    }
    public function testInvalidValueType()
    {
        $this->expectException(InvalidValue::class);
        $declaration = new IndentSize('true');
    }
    public function testInvalidValueValue()
    {
        $this->expectException(InvalidValue::class);
        $declaration = new IndentSize('four');
    }
    public function testInvalidNegativeIntegerValue()
    {
        $this->expectException(InvalidValue::class);
        $declaration = new IndentSize('-1');
    }
}
