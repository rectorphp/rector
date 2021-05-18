<?php

namespace RectorPrefix20210518\Helmich\TypoScriptParser\Tests\Unit\Parser\AST;

use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Statement;
use RectorPrefix20210518\PHPUnit\Framework\TestCase;
class StatementTest extends \RectorPrefix20210518\PHPUnit\Framework\TestCase
{
    public function dataForInvalidSourceLines()
    {
        (yield [0]);
        (yield [0.1]);
        (yield [-0.1]);
        (yield [-1]);
        (yield [-\PHP_INT_MAX]);
    }
    /**
     * @dataProvider dataForInvalidSourceLines
     * @param $invalidSourceLine
     */
    public function testInvalidSourceLineThrowsException($invalidSourceLine)
    {
        $this->expectException(\InvalidArgumentException::class);
        $statement = $this->getMockBuilder(\RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Statement::class)->setConstructorArgs([$invalidSourceLine])->enableOriginalConstructor()->getMockForAbstractClass();
    }
}
