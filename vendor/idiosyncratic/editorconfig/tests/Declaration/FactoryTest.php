<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Idiosyncratic\EditorConfig\Declaration;

use DomainException;
use RectorPrefix20210510\PHPUnit\Framework\TestCase;
use RuntimeException;
class FactoryTest extends TestCase
{
    public function testOfficialDeclarations()
    {
        $declarations = ['indent_style' => 'space', 'indent_size' => '4', 'tab_width' => '4', 'end_of_line' => 'lf', 'charset' => 'utf-8', 'trim_trailing_whitespace' => 'true', 'insert_final_newline' => 'false', 'max_line_length' => 'off'];
        $factory = new Factory();
        foreach ($declarations as $key => $value) {
            $declaration = $factory->getDeclaration($key, $value);
            $this->assertEquals($key, $declaration->getName());
        }
    }
    public function testUnsetDeclaration()
    {
        $factory = new Factory();
        $indentSize = $factory->getDeclaration('indent_size', 'unset');
        $this->assertInstanceOf(UnsetDeclaration::class, $indentSize);
    }
    public function testUnknownDeclaration()
    {
        $factory = new Factory();
        $justification = $factory->getDeclaration('justification', 'left');
        $this->assertInstanceOf(GenericDeclaration::class, $justification);
    }
}
