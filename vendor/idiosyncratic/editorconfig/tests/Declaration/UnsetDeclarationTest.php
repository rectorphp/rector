<?php

declare (strict_types=1);
namespace RectorPrefix20220507\Idiosyncratic\EditorConfig\Declaration;

use DomainException;
use ErrorException;
use RectorPrefix20220507\PHPUnit\Framework\TestCase;
use RuntimeException;
class UnsetDeclarationTest extends \RectorPrefix20220507\PHPUnit\Framework\TestCase
{
    public function testDeclaration()
    {
        $declaration = new \RectorPrefix20220507\Idiosyncratic\EditorConfig\Declaration\UnsetDeclaration('indent_style');
        $this->assertEquals('indent_style', $declaration->getName());
        $this->assertNull($declaration->getValue());
        $this->assertEquals('indent_style=unset', (string) $declaration);
    }
}
