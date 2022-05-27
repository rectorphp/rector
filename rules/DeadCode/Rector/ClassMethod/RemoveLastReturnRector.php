<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
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
final class RemoveLastReturnRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeNestingScope\ContextAnalyzer
     */
    private $contextAnalyzer;
    public function __construct(ContextAnalyzer $contextAnalyzer)
    {
        $this->contextAnalyzer = $contextAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove very last `return` that has no meaning', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [ClassMethod::class, Function_::class, Closure::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        // last node and last return
        $lastNode = $this->betterNodeFinder->findLastInstanceOf((array) $node->stmts, Node::class);
        $lastReturn = $this->betterNodeFinder->findLastInstanceOf((array) $node->stmts, Return_::class);
        if (!$lastReturn instanceof Return_) {
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
