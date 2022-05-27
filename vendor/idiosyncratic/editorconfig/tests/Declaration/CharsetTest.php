<?php

declare (strict_types=1);
namespace RectorPrefix20220527\Idiosyncratic\EditorConfig\Declaration;

use RectorPrefix20220527\Idiosyncratic\EditorConfig\Exception\InvalidValue;
use RectorPrefix20220527\PHPUnit\Framework\TestCase;
use RuntimeException;
class CharsetTest extends TestCase
{
    public function testValidValues()
    {
        foreach (Charset::CHARSETS as $charset) {
            $declaration = new Charset($charset);
            $this->assertEquals(\sprintf('charset=%s', $charset), (string) $declaration);
        }
    }
    public function testInvalidValue()
    {
        $this->expectException(InvalidValue::class);
        $declaration = new Charset('true');
        $this->expectException(InvalidValue::class);
        $declaration = new Charset('spaces');
    }
}
