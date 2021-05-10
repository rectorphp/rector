<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Idiosyncratic\EditorConfig\Declaration;

use DomainException;
use RectorPrefix20210510\PHPUnit\Framework\TestCase;
use RuntimeException;
class TrimTrailingWhitespaceTest extends TestCase
{
    public function testValidValues()
    {
        $declaration = new TrimTrailingWhitespace('false');
        $this->assertEquals('trim_trailing_whitespace=false', (string) $declaration);
        $declaration = new TrimTrailingWhitespace('true');
        $this->assertEquals('trim_trailing_whitespace=true', (string) $declaration);
    }
    public function testInvalidIntValue()
    {
        $this->expectException(DomainException::class);
        $declaration = new TrimTrailingWhitespace('4');
    }
    public function testInvalidStringValue()
    {
        $this->expectException(DomainException::class);
        $declaration = new TrimTrailingWhitespace('four');
    }
}
