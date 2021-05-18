<?php

namespace RectorPrefix20210518\Helmich\TypoScriptParser\Tests\Unit\Parser;

use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\ParseError;
use RectorPrefix20210518\PHPUnit\Framework\TestCase;
class ParseErrorTest extends \RectorPrefix20210518\PHPUnit\Framework\TestCase
{
    /** @var ParseError */
    private $exc;
    public function setUp() : void
    {
        $this->exc = new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\ParseError('foobar', 1234, 4321);
    }
    public function testCanSetSourceLine()
    {
        $this->assertEquals(4321, $this->exc->getSourceLine());
    }
    public function testCanSetMessage()
    {
        $this->assertEquals('foobar', $this->exc->getMessage());
    }
}
