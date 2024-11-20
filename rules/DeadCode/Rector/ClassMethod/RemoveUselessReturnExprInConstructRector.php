<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeVisitor;
use Rector\NodeAnalyzer\ExprAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessReturnExprInConstructRector\RemoveUselessReturnExprInConstructRectorTest
 */
final class RemoveUselessReturnExprInConstructRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ExprAnalyzer $exprAnalyzer;
    public function __construct(ExprAnalyzer $exprAnalyzer)
    {
        $this->exprAnalyzer = $exprAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove useless return Expr in __construct()', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct()
    {
        if (rand(0, 1)) {
            $this->init();
            return true;
        }

        if (rand(2, 3)) {
            return parent::construct();
        }

        $this->execute();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct()
    {
        if (rand(0, 1)) {
            $this->init();
            return;
        }

        if (rand(2, 3)) {
            parent::construct();
            return;
        }

        $this->execute();
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        if (!$this->isName($node, MethodName::CONSTRUCT)) {
            return null;
        }
        $hasChanged = \false;
        $this->traverseNodesWithCallable($node->stmts, function (Node $subNode) use(&$hasChanged) {
            if ($subNode instanceof Class_ || $subNode instanceof Function_ || $subNode instanceof Closure) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$subNode instanceof Return_) {
                return null;
            }
            if (!$subNode->expr instanceof Expr) {
                return null;
            }
            $hasChanged = \true;
            if ($this->exprAnalyzer->isDynamicExpr($subNode->expr)) {
                return [new Expression($subNode->expr), new Return_()];
            }
            $subNode->expr = null;
            return $subNode;
        });
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
