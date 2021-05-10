<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Stmt\If_;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\ConditionEvaluator;
use Rector\DeadCode\ConditionResolver;
use Rector\DeadCode\Contract\ConditionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/function.version-compare.php
 *
 * @see \Rector\Tests\DeadCode\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector\UnwrapFutureCompatibleIfPhpVersionRectorTest
 */
final class UnwrapFutureCompatibleIfPhpVersionRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var ConditionEvaluator
     */
    private $conditionEvaluator;
    /**
     * @var ConditionResolver
     */
    private $conditionResolver;
    public function __construct(\Rector\DeadCode\ConditionEvaluator $conditionEvaluator, \Rector\DeadCode\ConditionResolver $conditionResolver)
    {
        $this->conditionEvaluator = $conditionEvaluator;
        $this->conditionResolver = $conditionResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove php version checks if they are passed', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
// current PHP: 7.2
if (version_compare(PHP_VERSION, '7.2', '<')) {
    return 'is PHP 7.1-';
} else {
    return 'is PHP 7.2+';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
// current PHP: 7.2
return 'is PHP 7.2+';
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ((bool) $node->elseifs) {
            return null;
        }
        $condition = $this->conditionResolver->resolveFromExpr($node->cond);
        if (!$condition instanceof \Rector\DeadCode\Contract\ConditionInterface) {
            return null;
        }
        $result = $this->conditionEvaluator->evaluate($condition);
        if ($result === null) {
            return null;
        }
        // if is skipped
        if ($result) {
            $this->refactorIsMatch($node);
        } else {
            $this->refactorIsNotMatch($node);
        }
        return $node;
    }
    private function refactorIsMatch(\PhpParser\Node\Stmt\If_ $if) : void
    {
        if ((bool) $if->elseifs) {
            return;
        }
        $this->unwrapStmts($if->stmts, $if);
        $this->removeNode($if);
    }
    private function refactorIsNotMatch(\PhpParser\Node\Stmt\If_ $if) : void
    {
        // no else → just remove the node
        if ($if->else === null) {
            $this->removeNode($if);
            return;
        }
        // else is always used
        $this->unwrapStmts($if->else->stmts, $if);
        $this->removeNode($if);
    }
}
