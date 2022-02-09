<?php

declare (strict_types=1);
namespace RectorPrefix20220209\Idiosyncratic\EditorConfig\Declaration;

use RectorPrefix20220209\PHPUnit\Framework\TestCase;
class GenericDeclarationTest extends \RectorPrefix20220209\PHPUnit\Framework\TestCase
{
    public function testGetName() : void
    {
        $declaration = new \RectorPrefix20220209\Idiosyncratic\EditorConfig\Declaration\GenericDeclaration('declaration', 'string');
        $this->assertEquals('declaration', $declaration->getName());
    }
    public function testGetValue() : void
    {
        $declaration = new \RectorPrefix20220209\Idiosyncratic\EditorConfig\Declaration\GenericDeclaration('declaration', 'string');
        $this->assertIsString($declaration->getValue());
        $this->assertEquals('string', $declaration->getValue());
        $declaration = new \RectorPrefix20220209\Idiosyncratic\EditorConfig\Declaration\GenericDeclaration('declaration', '1');
        $this->assertIsInt($declaration->getValue());
        $this->assertSame(1, $declaration->getValue());
        $this->assertSame('1', $declaration->getStringValue());
        $declaration = new \RectorPrefix20220209\Idiosyncratic\EditorConfig\Declaration\GenericDeclaration('declaration', 'true');
        $this->assertIsBool($declaration->getValue());
        $this->assertTrue($declaration->getValue());
        $this->assertSame('true', $declaration->getStringValue());
        $declaration = new \RectorPrefix20220209\Idiosyncratic\EditorConfig\Declaration\GenericDeclaration('declaration', '1.1');
        $this->assertIsString($declaration->getValue());
        $this->assertSame('1.1', $declaration->getValue());
        $this->assertSame('1.1', $declaration->getStringValue());
    }
    public function testToString() : void
    {
        $declaration = new \RectorPrefix20220209\Idiosyncratic\EditorConfig\Declaration\GenericDeclaration('declaration', 'string');
        $this->assertEquals('declaration=string', (string) $declaration);
    }
}
