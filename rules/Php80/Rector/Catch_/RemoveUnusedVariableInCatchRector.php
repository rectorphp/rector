<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\Catch_;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Catch_;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/non-capturing_catches
 *
 * @see \Rector\Tests\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector\RemoveUnusedVariableInCatchRectorTest
 */
final class RemoveUnusedVariableInCatchRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer
     */
    private $exprUsedInNodeAnalyzer;
    public function __construct(\Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer $exprUsedInNodeAnalyzer)
    {
        $this->exprUsedInNodeAnalyzer = $exprUsedInNodeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove unused variable in catch()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        try {
        } catch (Throwable $notUsedThrowable) {
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        try {
        } catch (Throwable) {
        }
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
        return [\PhpParser\Node\Stmt\Catch_::class];
    }
    /**
     * @param Catch_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $caughtVar = $node->var;
        if (!$caughtVar instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        if ($this->isVariableUsedInStmts($node->stmts, $caughtVar)) {
            return null;
        }
        if ($this->isVariableUsedNext($node, $caughtVar)) {
            return null;
        }
        $node->var = null;
        return $node;
    }
    /**
     * @param Node[] $nodes
     */
    private function isVariableUsedInStmts(array $nodes, \PhpParser\Node\Expr\Variable $variable) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($nodes, function (\PhpParser\Node $node) use($variable) : bool {
            return $this->exprUsedInNodeAnalyzer->isUsed($node, $variable);
        });
    }
    private function isVariableUsedNext(\PhpParser\Node\Stmt\Catch_ $catch, \PhpParser\Node\Expr\Variable $variable) : bool
    {
        return (bool) $this->betterNodeFinder->findFirstNext($catch, function (\PhpParser\Node $node) use($variable) : bool {
            return $this->exprUsedInNodeAnalyzer->isUsed($node, $variable);
        });
    }
}
