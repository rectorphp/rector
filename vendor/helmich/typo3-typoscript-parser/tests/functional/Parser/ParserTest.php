<?php

declare (strict_types=1);
namespace RectorPrefix20210518\Helmich\TypoScriptParser\Tests\Functional\Parser;

use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\ParseError;
use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\Parser;
use RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\Tokenizer;
use RectorPrefix20210518\PHPUnit\Framework\TestCase;
class ParserTest extends \RectorPrefix20210518\PHPUnit\Framework\TestCase
{
    /** @var Parser */
    private $parser;
    public function setUp() : void
    {
        $this->parser = new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\Parser(new \RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\Tokenizer());
    }
    public function dataForParserTest()
    {
        $files = \glob(__DIR__ . '/Fixtures/*/*.typoscript');
        $testCases = [];
        foreach ($files as $file) {
            $outputFile = \str_replace('.typoscript', '.php', $file);
            /** @noinspection PhpIncludeInspection */
            $output = (include $outputFile);
            $testCases[\str_replace(".typoscript", "", \basename($file))] = [$file, $output];
        }
        return $testCases;
    }
    public function dataForParseErrorTest()
    {
        (yield ["foo {\n    bar = 1"]);
        (yield ["foo > bar"]);
        (yield ["foo {\n    [globalString = GP:foo=1]\n    bar =1 \n    [global]\n}"]);
        (yield ["[globalString = GP:foo=1]\nbar = 1\n[else]\nbar = 2\n[else]\nbar = 3\n[global]"]);
        //yield ["[globalString = GP:foo=1]\nbar = 1\n[else][else]\nbar = 3\n[global]"];
        (yield ["foo = 1\n}"]);
        (yield ["foo = 1\n[end]"]);
        (yield ["foo :="]);
        (yield ["foo := foobar"]);
        (yield ["foo <"]);
        (yield ["foo < hello world"]);
    }
    /**
     * @dataProvider dataForParserTest
     * @testdox Code is parsed into correct AST
     * @param $inputFile
     * @param $expectedAST
     */
    public function testCodeIsParsedIntoCorrectAST($inputFile, $expectedAST)
    {
        $ast = $this->parser->parseStream($inputFile);
        $this->assertEquals($expectedAST, $ast);
        //        $this->assertSame($expectedAST, $ast);
    }
    /**
     * @dataProvider dataForParseErrorTest
     * @param $inputCode
     */
    public function testBadCodeCausesParserError($inputCode)
    {
        $this->expectException(\RectorPrefix20210518\Helmich\TypoScriptParser\Parser\ParseError::class);
        $this->parser->parseString($inputCode);
    }
}
