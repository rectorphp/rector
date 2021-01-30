<?php

declare(strict_types=1);

namespace Rector\Privatization\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Privatization\Tests\Rector\Property\PrivatizeFinalClassPropertyRector\PrivatizeFinalClassPropertyRectorTest
 */
final class PrivatizeFinalClassPropertyRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change property to private if possible', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    protected $value;
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    private $value;
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
        return [Property::class];
    }

    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return null;
        }

        if (! $classLike->isFinal()) {
            return null;
        }

        if ($this->shouldSkipProperty($node)) {
            return null;
        }

        if ($classLike->extends === null) {
            $this->visibilityManipulator->makePrivate($node);
            return $node;
        }

        if ($this->isPropertyVisibilityGuardedByParent($node, $classLike)) {
            return null;
        }

        $this->visibilityManipulator->makePrivate($node);

        return $node;
    }

    private function shouldSkipProperty(Property $property): bool
    {
        if (count($property->props) !== 1) {
            return true;
        }

        return ! $property->isProtected();
    }

    private function isPropertyVisibilityGuardedByParent(Property $property, Class_ $class): bool
    {
        if ($class->extends === null) {
            return false;
        }

        $parentClasses = $this->getParentClasses($class);
        $propertyName = $this->getName($property);

        foreach ($parentClasses as $parentClass) {
            if (property_exists($parentClass, $propertyName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string[]
     */
    private function getParentClasses(Class_ $class): array
    {
        /** @var string $className */
        $className = $this->getName($class);

        $classParents = class_parents($className);
        if ($classParents === false) {
            return [];
        }

        return $classParents;
    }
}
