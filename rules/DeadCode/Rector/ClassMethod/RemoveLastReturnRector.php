<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeNestingScope\ContextAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveLastReturnRector\RemoveLastReturnRectorTest
 */
final class RemoveLastReturnRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeNestingScope\ContextAnalyzer
     */
    private $contextAnalyzer;
    public function __construct(\Rector\NodeNestingScope\ContextAnalyzer $contextAnalyzer)
    {
        $this->contextAnalyzer = $contextAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove very last `return` that has no meaning', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
function some_function($value)
{
    if ($value === 1000) {
        return;
    }

    if ($value) {
        return;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function some_function($value)
{
    if ($value === 1000) {
        return;
    }

    if ($value) {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        // last node and last return
        $lastNode = $this->betterNodeFinder->findLastInstanceOf((array) $node->stmts, \PhpParser\Node::class);
        $lastReturn = $this->betterNodeFinder->findLastInstanceOf((array) $node->stmts, \PhpParser\Node\Stmt\Return_::class);
        if (!$lastReturn instanceof \PhpParser\Node\Stmt\Return_) {
            return null;
        }
        if (!$lastNode instanceof \PhpParser\Node) {
            return null;
        }
        if ($lastNode !== $lastReturn) {
            return null;
        }
        if ($this->contextAnalyzer->isInLoop($lastReturn)) {
            return null;
        }
        $this->removeNode($lastReturn);
        return null;
    }
}
