<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\Constant\ConstantBooleanType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector\RemoveAlwaysTrueIfConditionRectorTest
 */
final class RemoveAlwaysTrueIfConditionRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove if condition that is always true', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function go()
    {
        if (1 === 1) {
            return 'yes';
        }

        return 'no';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function go()
    {
        return 'yes';

        return 'no';
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
        return [\PhpParser\Node\Stmt\If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node->else !== null) {
            return null;
        }
        // just one if
        if ($node->elseifs !== []) {
            return null;
        }
        $conditionStaticType = $this->getType($node->cond);
        if (!$conditionStaticType instanceof \PHPStan\Type\Constant\ConstantBooleanType) {
            return null;
        }
        if (!$conditionStaticType->getValue()) {
            return null;
        }
        if (\count($node->stmts) !== 1) {
            // unable to handle now
            return null;
        }
        return $node->stmts[0];
    }
}
