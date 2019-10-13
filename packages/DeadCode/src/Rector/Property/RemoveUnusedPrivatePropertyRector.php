<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Manipulator\PropertyManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\RemoveUnusedPrivatePropertyRectorTest
 */
final class RemoveUnusedPrivatePropertyRector extends AbstractRector
{
    /**
     * @var PropertyManipulator
     */
    private $propertyManipulator;

    public function __construct(PropertyManipulator $propertyManipulator)
    {
        $this->propertyManipulator = $propertyManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove unused private properties', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    private $property;
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
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
        if ($this->shouldSkipProperty($node)) {
            return null;
        }

        $propertyFetches = $this->propertyManipulator->getAllPropertyFetch($node);

        // never used
        if ($propertyFetches === []) {
            $this->removeNode($node);
        }

        $uselessAssigns = $this->resolveUselessAssignNode($propertyFetches);
        if (count($uselessAssigns) > 0) {
            $this->removeNode($node);
            foreach ($uselessAssigns as $uselessAssign) {
                $this->removeNode($uselessAssign);
            }
        }

        return $node;
    }

    /**
     * Matches all-only: "$this->property = x"
     * If these is ANY OTHER use of property, e.g. process($this->property), it returns []
     *
     * @param PropertyFetch[]|StaticPropertyFetch[] $propertyFetches
     * @return Assign[]
     */
    private function resolveUselessAssignNode(array $propertyFetches): array
    {
        $uselessAssigns = [];

        foreach ($propertyFetches as $propertyFetch) {
            $propertyFetchParentNode = $propertyFetch->getAttribute(AttributeKey::PARENT_NODE);

            if ($propertyFetchParentNode instanceof Assign && $propertyFetchParentNode->var === $propertyFetch) {
                $uselessAssigns[] = $propertyFetchParentNode;
            } else {
                // it is used another way as well → nothing to remove
                return [];
            }
        }

        return $uselessAssigns;
    }

    private function shouldSkipProperty(Property $property): bool
    {
        if (! $property->isPrivate()) {
            return true;
        }

        /** @var Class_|Interface_|Trait_|null $classNode */
        $classNode = $property->getAttribute(AttributeKey::CLASS_NODE);

        if ($classNode === null || $classNode instanceof Trait_ || $classNode instanceof Interface_) {
            return true;
        }

        if ($this->isDoctrineProperty($property)) {
            return true;
        }

        if ($classNode->isAnonymous()) {
            return true;
        }

        if (count($property->props) !== 1) {
            return true;
        }

        // has class $this->$variable call?

        /** @var Node\Stmt\ClassLike $class */
        $class = $property->getAttribute(AttributeKey::CLASS_NODE);
        $hasMagicPropertyFetch = (bool) $this->betterNodeFinder->findFirst($class->stmts, function (Node $node): bool {
            if (! $node instanceof PropertyFetch) {
                return false;
            }

            return $node->name instanceof Expr;
        });

        if ($hasMagicPropertyFetch) {
            return true;
        }

        return false;
    }
}
