<?php declare(strict_types=1);

namespace Rector\DeadCode\Rector\Property;

use PhpParser\Node;
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
        if (! $node->isPrivate()) {
            return null;
        }

        /** @var Class_|Interface_|Trait_|null $classNode */
        $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode === null || $classNode instanceof Trait_ || $classNode instanceof Interface_) {
            return null;
        }

        if ($classNode->isAnonymous()) {
            return null;
        }

        if (count($node->props) !== 1) {
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
}
