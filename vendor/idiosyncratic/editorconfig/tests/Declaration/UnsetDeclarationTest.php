<?php

declare (strict_types=1);
namespace RectorPrefix20220501\Idiosyncratic\EditorConfig\Declaration;

use DomainException;
use ErrorException;
use RectorPrefix20220501\PHPUnit\Framework\TestCase;
use RuntimeException;
class UnsetDeclarationTest extends \RectorPrefix20220501\PHPUnit\Framework\TestCase
{
    public function testDeclaration()
    {
        $declaration = new \RectorPrefix20220501\Idiosyncratic\EditorConfig\Declaration\UnsetDeclaration('indent_style');
        $this->assertEquals('indent_style', $declaration->getName());
        $this->assertNull($declaration->getValue());
        $this->assertEquals('indent_style=unset', (string) $declaration);
    }
}
