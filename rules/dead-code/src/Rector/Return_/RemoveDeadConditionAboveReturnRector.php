<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Return_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\Constant\ConstantBooleanType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\Return_\RemoveDeadConditionAboveReturnRector\RemoveDeadConditionAboveReturnRectorTest
 */
final class RemoveDeadConditionAboveReturnRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove dead condition above return', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function go()
    {
        if (1 === 1) {
            return 'yes';
        }

        return 'yes';
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function go()
    {
        return 'yes';
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Return_::class];
    }

    /**
     * @param Return_ $node
     */
    public function refactor(Node $node): ?Node
    {
        return $node;
    }
}
