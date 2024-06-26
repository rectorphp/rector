<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\If_;
use PhpParser\NodeTraverser;
use Rector\DeadCode\ConditionEvaluator;
use Rector\DeadCode\ConditionResolver;
use Rector\DeadCode\Contract\ConditionInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector\UnwrapFutureCompatibleIfPhpVersionRectorTest
 */
final class UnwrapFutureCompatibleIfPhpVersionRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\DeadCode\ConditionEvaluator
     */
    private $conditionEvaluator;
    /**
     * @readonly
     * @var \Rector\DeadCode\ConditionResolver
     */
    private $conditionResolver;
    public function __construct(ConditionEvaluator $conditionEvaluator, ConditionResolver $conditionResolver)
    {
        $this->conditionEvaluator = $conditionEvaluator;
        $this->conditionResolver = $conditionResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove php version checks if they are passed', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [If_::class];
    }
    /**
     * @param If_ $node
     * @return Stmt[]|null|int
     */
    public function refactor(Node $node)
    {
        if ($node->elseifs !== []) {
            return null;
        }
        $condition = $this->conditionResolver->resolveFromExpr($node->cond);
        if (!$condition instanceof ConditionInterface) {
            return null;
        }
        $result = $this->conditionEvaluator->evaluate($condition);
        if ($result === null) {
            return null;
        }
        // if is skipped
        if ($result === \true) {
            return $this->refactorIsMatch($node);
        }
        if ($result > 0) {
            return $this->refactorIsMatch($node);
        }
        return $this->refactorIsNotMatch($node);
    }
    /**
     * @return Stmt[]|null
     */
    private function refactorIsMatch(If_ $if) : ?array
    {
        if ($if->elseifs !== []) {
            return null;
        }
        return $if->stmts;
    }
    /**
     * @return Stmt[]|int
     */
    private function refactorIsNotMatch(If_ $if)
    {
        // no else → just remove the node
        if (!$if->else instanceof Else_) {
            return NodeTraverser::REMOVE_NODE;
        }
        // else is always used
        return $if->else->stmts;
    }
}
