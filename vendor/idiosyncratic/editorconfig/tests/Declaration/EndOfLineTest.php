<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Idiosyncratic\EditorConfig\Declaration;

use DomainException;
use RectorPrefix20210510\PHPUnit\Framework\TestCase;
use RuntimeException;
class EndOfLineTest extends TestCase
{
    public function testValidValues()
    {
        foreach (EndOfLine::LINE_ENDINGS as $eol) {
            $declaration = new EndOfLine($eol);
            $this->assertEquals(\sprintf('end_of_line=%s', $eol), (string) $declaration);
        }
    }
    public function testInvalidValues()
    {
        $this->expectException(DomainException::class);
        $declaration = new EndOfLine('true');
        $this->expectException(DomainException::class);
        $declaration = new EndOfLine('spaces');
    }
}
