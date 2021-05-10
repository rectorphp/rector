<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Idiosyncratic\EditorConfig\Declaration;

use RectorPrefix20210510\PHPUnit\Framework\TestCase;
class GenericDeclarationTest extends TestCase
{
    public function testGetName() : void
    {
        $declaration = new GenericDeclaration('declaration', 'string');
        $this->assertEquals('declaration', $declaration->getName());
    }
    public function testGetValue() : void
    {
        $declaration = new GenericDeclaration('declaration', 'string');
        $this->assertIsString($declaration->getValue());
        $this->assertEquals('string', $declaration->getValue());
        $declaration = new GenericDeclaration('declaration', '1');
        $this->assertIsInt($declaration->getValue());
        $this->assertSame(1, $declaration->getValue());
        $this->assertSame('1', $declaration->getStringValue());
        $declaration = new GenericDeclaration('declaration', 'true');
        $this->assertIsBool($declaration->getValue());
        $this->assertTrue($declaration->getValue());
        $this->assertSame('true', $declaration->getStringValue());
        $declaration = new GenericDeclaration('declaration', '1.1');
        $this->assertIsString($declaration->getValue());
        $this->assertSame('1.1', $declaration->getValue());
        $this->assertSame('1.1', $declaration->getStringValue());
    }
    public function testToString() : void
    {
        $declaration = new GenericDeclaration('declaration', 'string');
        $this->assertEquals('declaration=string', (string) $declaration);
    }
}
