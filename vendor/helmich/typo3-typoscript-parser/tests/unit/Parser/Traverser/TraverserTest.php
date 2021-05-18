<?php

namespace RectorPrefix20210518\Helmich\TypoScriptParser\Tests\Unit\Parser\Traverser;

use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ConditionalStatement;
use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\NestedAssignment;
use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath;
use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Scalar;
use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\Traverser\Traverser;
use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\Traverser\Visitor;
use RectorPrefix20210518\PHPUnit\Framework\TestCase;
class TraverserTest extends \RectorPrefix20210518\PHPUnit\Framework\TestCase
{
    private $tree;
    /** @var Traverser */
    private $traverser;
    public function setUp() : void
    {
        $this->tree = [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('foo', 'foo'), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Scalar('bar'), 1), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ConditionalStatement('[globalVar = GP:foo=1]', [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('foo', 'foo'), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Scalar('bar'), 2)], [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('foo', 'foo'), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Scalar('baz'), 4)], 2), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\NestedAssignment(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('bar', 'bar'), [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('bar.baz', 'baz'), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Scalar('foo'), 1)], 3)];
        $this->traverser = new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\Traverser\Traverser($this->tree);
    }
    public function testHookFunctionsAreCalled()
    {
        $visitor = $this->prophesize(\RectorPrefix20210518\Helmich\TypoScriptParser\Parser\Traverser\Visitor::class);
        $visitor->enterTree($this->tree)->shouldBeCalled();
        $visitor->exitTree($this->tree)->shouldBeCalled();
        $visitor->enterNode($this->tree[0])->shouldBeCalled();
        $visitor->enterNode($this->tree[1])->shouldBeCalled();
        $visitor->enterNode($this->tree[1]->ifStatements[0])->shouldBeCalled();
        $visitor->enterNode($this->tree[1]->elseStatements[0])->shouldBeCalled();
        $visitor->enterNode($this->tree[2])->shouldBeCalled();
        $visitor->enterNode($this->tree[2]->statements[0])->shouldBeCalled();
        $visitor->exitNode($this->tree[0])->shouldBeCalled();
        $visitor->exitNode($this->tree[1])->shouldBeCalled();
        $visitor->exitNode($this->tree[1]->ifStatements[0])->shouldBeCalled();
        $visitor->exitNode($this->tree[1]->elseStatements[0])->shouldBeCalled();
        $visitor->exitNode($this->tree[2])->shouldBeCalled();
        $visitor->exitNode($this->tree[2]->statements[0])->shouldBeCalled();
        $this->traverser->addVisitor($visitor->reveal());
        $this->traverser->walk();
    }
}
