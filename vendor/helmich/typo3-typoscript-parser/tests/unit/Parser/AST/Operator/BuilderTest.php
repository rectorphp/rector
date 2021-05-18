<?php

namespace RectorPrefix20210518\Helmich\TypoScriptParser\Tests\Unit\Parser\AST\Operator;

use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath;
use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\Builder as OperatorBuilder;
use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\Copy;
use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\ObjectCreation;
use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Scalar;
use RectorPrefix20210518\PHPUnit\Framework\TestCase;
class BuilderTest extends \RectorPrefix20210518\PHPUnit\Framework\TestCase
{
    /** @var OperatorBuilder */
    private $opBuilder;
    public function setUp() : void
    {
        $this->opBuilder = new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\Builder();
    }
    public function testObjectCreationIsBuilt()
    {
        $op = $this->opBuilder->objectCreation($foo = new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('foo', 'foo'), $text = new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Scalar('TEXT'), 1);
        assertThat($op, isInstanceOf(\RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\ObjectCreation::class));
        assertThat($op->object, identicalTo($foo));
        assertThat($op->value, identicalTo($text));
    }
    public function testCopyOperatorIsBuilt()
    {
        $op = $this->opBuilder->copy($foo = new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('foo', 'foo'), $bar = new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('bar', 'bar'), 1);
        assertThat($op, isInstanceOf(\RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\Copy::class));
        assertThat($op->object, identicalTo($foo));
        assertThat($op->target, identicalTo($bar));
    }
    public function testPassesExcessParameters()
    {
        $op = $this->opBuilder->copy($foo = new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('foo', 'foo'), $bar = new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('bar', 'bar'), 1, 'foo');
        assertThat($op, isInstanceOf(\RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\Copy::class));
        assertThat($op->object, identicalTo($foo));
        assertThat($op->target, identicalTo($bar));
    }
}
