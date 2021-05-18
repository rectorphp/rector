<?php

namespace RectorPrefix20210518\Helmich\TypoScriptParser\Tests\Unit\Tokenizer\Printer;

use RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\Printer\StructuredTokenPrinter;
use RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\Token;
use RectorPrefix20210518\PHPUnit\Framework\TestCase;
class StructuredTokenPrinterTest extends \RectorPrefix20210518\PHPUnit\Framework\TestCase
{
    /** @var StructuredTokenPrinter */
    private $printer;
    public function setUp() : void
    {
        $this->printer = new \RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\Printer\StructuredTokenPrinter();
    }
    public function testTokensArePrinted()
    {
        $expectedOutput = <<<OUT
           OBJ_IDENT foo
                  WS ' '
           OP_ASSIGN '='
                  WS ' '
              RVALUE bar
                  WS "\\n"
           OBJ_IDENT bar
                  WS ' '
           OP_ASSIGN '='
                  WS ' '
              RVALUE bar
                  WS "\\n"
OUT;
        $tokens = [new \RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\Token(\RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\Token::TYPE_OBJECT_IDENTIFIER, "foo", 1, 1), new \RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\Token(\RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\Token::TYPE_WHITESPACE, " ", 1, 4), new \RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\Token(\RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\Token::TYPE_OPERATOR_ASSIGNMENT, "=", 1, 5), new \RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\Token(\RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\Token::TYPE_WHITESPACE, " ", 1, 6), new \RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\Token(\RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\Token::TYPE_RIGHTVALUE, "bar", 1, 7), new \RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\Token(\RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\Token::TYPE_WHITESPACE, "\n", 1, 10), new \RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\Token(\RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\Token::TYPE_OBJECT_IDENTIFIER, "bar", 2, 1), new \RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\Token(\RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\Token::TYPE_WHITESPACE, " ", 2, 4), new \RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\Token(\RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\Token::TYPE_OPERATOR_ASSIGNMENT, "=", 2, 5), new \RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\Token(\RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\Token::TYPE_WHITESPACE, " ", 2, 6), new \RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\Token(\RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\Token::TYPE_RIGHTVALUE, "bar", 2, 7), new \RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\Token(\RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\Token::TYPE_WHITESPACE, "\n", 2, 10)];
        $output = $this->printer->printTokenStream($tokens);
        assertThat(\trim($output), equalTo(\trim($expectedOutput)));
    }
}
