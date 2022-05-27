<?php

declare (strict_types=1);
namespace RectorPrefix20220527\Idiosyncratic\EditorConfig\Declaration;

use RectorPrefix20220527\Idiosyncratic\EditorConfig\Exception\InvalidValue;
use RectorPrefix20220527\PHPUnit\Framework\TestCase;
class TabWidthTest extends TestCase
{
    public function testValidValues()
    {
        $declaration = new TabWidth('4');
        $this->assertEquals('tab_width=4', (string) $declaration);
        $this->assertSame(4, $declaration->getValue());
    }
    public function testInvalidValues()
    {
        $this->expectException(InvalidValue::class);
        $declaration = new TabWidth('true');
        $this->expectException(InvalidValue::class);
        $declaration = new TabWidth('four');
        $this->expectException(InvalidValue::class);
        $declaration = new TabWidth('-1');
    }
}
