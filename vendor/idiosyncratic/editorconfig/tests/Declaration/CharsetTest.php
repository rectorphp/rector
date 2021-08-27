<?php

declare (strict_types=1);
namespace RectorPrefix20210827\Idiosyncratic\EditorConfig\Declaration;

use RectorPrefix20210827\Idiosyncratic\EditorConfig\Exception\InvalidValue;
use RectorPrefix20210827\PHPUnit\Framework\TestCase;
use RuntimeException;
class CharsetTest extends \RectorPrefix20210827\PHPUnit\Framework\TestCase
{
    public function testValidValues()
    {
        foreach (\RectorPrefix20210827\Idiosyncratic\EditorConfig\Declaration\Charset::CHARSETS as $charset) {
            $declaration = new \RectorPrefix20210827\Idiosyncratic\EditorConfig\Declaration\Charset($charset);
            $this->assertEquals(\sprintf('charset=%s', $charset), (string) $declaration);
        }
    }
    public function testInvalidValue()
    {
        $this->expectException(\RectorPrefix20210827\Idiosyncratic\EditorConfig\Exception\InvalidValue::class);
        $declaration = new \RectorPrefix20210827\Idiosyncratic\EditorConfig\Declaration\Charset('true');
        $this->expectException(\RectorPrefix20210827\Idiosyncratic\EditorConfig\Exception\InvalidValue::class);
        $declaration = new \RectorPrefix20210827\Idiosyncratic\EditorConfig\Declaration\Charset('spaces');
    }
}
