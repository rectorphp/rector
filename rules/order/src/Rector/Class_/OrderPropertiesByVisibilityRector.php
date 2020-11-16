<?php

declare(strict_types=1);

namespace Rector\Order\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use Rector\Order\Rector\AbstractConstantPropertyMethodOrderRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Order\Tests\Rector\Class_\OrderPropertiesByVisibilityRector\OrderPropertiesByVisibilityRectorTest
 */
final class OrderPropertiesByVisibilityRector extends AbstractConstantPropertyMethodOrderRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Orders properties by visibility', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    protected $protectedProperty;
    private $privateProperty;
    public $publicProperty;
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public $publicProperty;
    protected $protectedProperty;
    private $privateProperty;
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
        return [Class_::class, Trait_::class];
    }

    /**
     * @param Class_|Trait_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $currentPropertiesOrder = $this->stmtOrder->getStmtsOfTypeOrder($node, Property::class);
        $propertiesInDesiredOrder = $this->stmtVisibilitySorter->sortProperties($node);

        $oldToNewKeys = $this->stmtOrder->createOldToNewKeys($propertiesInDesiredOrder, $currentPropertiesOrder);

        // nothing to re-order
        if (! $this->hasOrderChanged($oldToNewKeys)) {
            return null;
        }

        return $this->stmtOrder->reorderClassStmtsByOldToNewKeys($node, $oldToNewKeys);
    }
}
