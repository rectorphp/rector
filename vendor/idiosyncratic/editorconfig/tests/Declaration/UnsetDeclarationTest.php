<?php

declare (strict_types=1);
namespace RectorPrefix20210912\Idiosyncratic\EditorConfig\Declaration;

use DomainException;
use ErrorException;
use RectorPrefix20210912\PHPUnit\Framework\TestCase;
use RuntimeException;
class UnsetDeclarationTest extends \RectorPrefix20210912\PHPUnit\Framework\TestCase
{
    public function testDeclaration()
    {
        $declaration = new \RectorPrefix20210912\Idiosyncratic\EditorConfig\Declaration\UnsetDeclaration('indent_style');
        $this->assertEquals('indent_style', $declaration->getName());
        $this->assertNull($declaration->getValue());
        $this->assertEquals('indent_style=unset', (string) $declaration);
    }
}
