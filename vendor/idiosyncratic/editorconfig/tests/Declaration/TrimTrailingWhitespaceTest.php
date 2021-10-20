<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Idiosyncratic\EditorConfig\Declaration;

use DomainException;
use RectorPrefix20211020\PHPUnit\Framework\TestCase;
use RuntimeException;
class TrimTrailingWhitespaceTest extends \RectorPrefix20211020\PHPUnit\Framework\TestCase
{
    public function testValidValues()
    {
        $declaration = new \RectorPrefix20211020\Idiosyncratic\EditorConfig\Declaration\TrimTrailingWhitespace('false');
        $this->assertEquals('trim_trailing_whitespace=false', (string) $declaration);
        $declaration = new \RectorPrefix20211020\Idiosyncratic\EditorConfig\Declaration\TrimTrailingWhitespace('true');
        $this->assertEquals('trim_trailing_whitespace=true', (string) $declaration);
    }
    public function testInvalidIntValue()
    {
        $this->expectException(\DomainException::class);
        $declaration = new \RectorPrefix20211020\Idiosyncratic\EditorConfig\Declaration\TrimTrailingWhitespace('4');
    }
    public function testInvalidStringValue()
    {
        $this->expectException(\DomainException::class);
        $declaration = new \RectorPrefix20211020\Idiosyncratic\EditorConfig\Declaration\TrimTrailingWhitespace('four');
    }
}
