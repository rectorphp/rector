<?php

declare (strict_types=1);
namespace RectorPrefix20220516\Idiosyncratic\EditorConfig\Declaration;

use RectorPrefix20220516\Idiosyncratic\EditorConfig\Exception\InvalidValue;
use RectorPrefix20220516\PHPUnit\Framework\TestCase;
class TabWidthTest extends \RectorPrefix20220516\PHPUnit\Framework\TestCase
{
    public function testValidValues()
    {
        $declaration = new \RectorPrefix20220516\Idiosyncratic\EditorConfig\Declaration\TabWidth('4');
        $this->assertEquals('tab_width=4', (string) $declaration);
        $this->assertSame(4, $declaration->getValue());
    }
    public function testInvalidValues()
    {
        $this->expectException(\RectorPrefix20220516\Idiosyncratic\EditorConfig\Exception\InvalidValue::class);
        $declaration = new \RectorPrefix20220516\Idiosyncratic\EditorConfig\Declaration\TabWidth('true');
        $this->expectException(\RectorPrefix20220516\Idiosyncratic\EditorConfig\Exception\InvalidValue::class);
        $declaration = new \RectorPrefix20220516\Idiosyncratic\EditorConfig\Declaration\TabWidth('four');
        $this->expectException(\RectorPrefix20220516\Idiosyncratic\EditorConfig\Exception\InvalidValue::class);
        $declaration = new \RectorPrefix20220516\Idiosyncratic\EditorConfig\Declaration\TabWidth('-1');
    }
}
