<?php

declare (strict_types=1);
namespace RectorPrefix20220501\Idiosyncratic\EditorConfig\Declaration;

use DomainException;
use RectorPrefix20220501\PHPUnit\Framework\TestCase;
use RuntimeException;
class FactoryTest extends \RectorPrefix20220501\PHPUnit\Framework\TestCase
{
    public function testOfficialDeclarations()
    {
        $declarations = ['indent_style' => 'space', 'indent_size' => '4', 'tab_width' => '4', 'end_of_line' => 'lf', 'charset' => 'utf-8', 'trim_trailing_whitespace' => 'true', 'insert_final_newline' => 'false', 'max_line_length' => 'off'];
        $factory = new \RectorPrefix20220501\Idiosyncratic\EditorConfig\Declaration\Factory();
        foreach ($declarations as $key => $value) {
            $declaration = $factory->getDeclaration($key, $value);
            $this->assertEquals($key, $declaration->getName());
        }
    }
    public function testUnsetDeclaration()
    {
        $factory = new \RectorPrefix20220501\Idiosyncratic\EditorConfig\Declaration\Factory();
        $indentSize = $factory->getDeclaration('indent_size', 'unset');
        $this->assertInstanceOf(\RectorPrefix20220501\Idiosyncratic\EditorConfig\Declaration\UnsetDeclaration::class, $indentSize);
    }
    public function testUnknownDeclaration()
    {
        $factory = new \RectorPrefix20220501\Idiosyncratic\EditorConfig\Declaration\Factory();
        $justification = $factory->getDeclaration('justification', 'left');
        $this->assertInstanceOf(\RectorPrefix20220501\Idiosyncratic\EditorConfig\Declaration\GenericDeclaration::class, $justification);
    }
}
