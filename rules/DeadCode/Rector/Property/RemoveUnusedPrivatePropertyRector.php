<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Contract\Rector\AllowEmptyConfigurableRectorInterface;
use Rector\Core\NodeManipulator\PropertyManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Removing\NodeManipulator\ComplexNodeRemover;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\RemoveUnusedPrivatePropertyRectorTest
 */
final class RemoveUnusedPrivatePropertyRector extends AbstractRector implements AllowEmptyConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const REMOVE_ASSIGN_SIDE_EFFECT = 'remove_assign_side_effect';

    /**
     * Default to true, which apply remove assign even has side effect.
     * Set to false will allow to skip when assign has side effect.
     */
    private bool $removeAssignSideEffect = true;

    public function __construct(
        private readonly PropertyManipulator $propertyManipulator,
        private readonly ComplexNodeRemover $complexNodeRemover,
    ) {
    }

    public function configure(array $configuration): void
    {
        $this->removeAssignSideEffect = $configuration[self::REMOVE_ASSIGN_SIDE_EFFECT] ?? (bool) current(
            $configuration
        );
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove unused private properties', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    private $property;
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
}
CODE_SAMPLE
,
                [
                    self::REMOVE_ASSIGN_SIDE_EFFECT => true,
                ]
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }

    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkipProperty($node)) {
            return null;
        }

        if ($this->propertyManipulator->isPropertyUsedInReadContext($node)) {
            return null;
        }

        $this->complexNodeRemover->removePropertyAndUsages($node, $this->removeAssignSideEffect);

        return $node;
    }

    private function shouldSkipProperty(Property $property): bool
    {
        if (count($property->props) !== 1) {
            return true;
        }

        if (! $property->isPrivate()) {
            return true;
        }

        $class = $this->betterNodeFinder->findParentType($property, Class_::class);
        return ! $class instanceof Class_;
    }
}
