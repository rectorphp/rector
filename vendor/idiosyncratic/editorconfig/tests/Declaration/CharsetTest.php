<?php

declare (strict_types=1);
namespace RectorPrefix20211226\Idiosyncratic\EditorConfig\Declaration;

use RectorPrefix20211226\Idiosyncratic\EditorConfig\Exception\InvalidValue;
use RectorPrefix20211226\PHPUnit\Framework\TestCase;
use RuntimeException;
class CharsetTest extends \RectorPrefix20211226\PHPUnit\Framework\TestCase
{
    public function testValidValues()
    {
        foreach (\RectorPrefix20211226\Idiosyncratic\EditorConfig\Declaration\Charset::CHARSETS as $charset) {
            $declaration = new \RectorPrefix20211226\Idiosyncratic\EditorConfig\Declaration\Charset($charset);
            $this->assertEquals(\sprintf('charset=%s', $charset), (string) $declaration);
        }
    }
    public function testInvalidValue()
    {
        $this->expectException(\RectorPrefix20211226\Idiosyncratic\EditorConfig\Exception\InvalidValue::class);
        $declaration = new \RectorPrefix20211226\Idiosyncratic\EditorConfig\Declaration\Charset('true');
        $this->expectException(\RectorPrefix20211226\Idiosyncratic\EditorConfig\Exception\InvalidValue::class);
        $declaration = new \RectorPrefix20211226\Idiosyncratic\EditorConfig\Declaration\Charset('spaces');
    }
}
