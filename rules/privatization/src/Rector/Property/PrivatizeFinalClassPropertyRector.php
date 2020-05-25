<?php

declare(strict_types=1);

namespace Rector\Privatization\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Privatization\Tests\Rector\Property\PrivatizeFinalClassPropertyRector\PrivatizeFinalClassPropertyRectorTest
 */
final class PrivatizeFinalClassPropertyRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change property to private if possible', [
            new CodeSample(
                <<<'PHP'
final class SomeClass
{
    protected $value;
}
PHP
,
                <<<'PHP'
final class SomeClass
{
    private $value;
}
PHP

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
        $class = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $class instanceof Class_) {
            return null;
        }

        if (! $class->isFinal()) {
            return null;
        }

        if ($this->shouldSkipProperty($node)) {
            return null;
        }

        if ($class->extends === null) {
            $this->makePrivate($node);
            return $node;
        }

        if ($this->isPropertyVisibilityGuardedByParent($node, $class)) {
            return null;
        }

        $this->makePrivate($node);

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

        return class_parents($className);
    }
}
