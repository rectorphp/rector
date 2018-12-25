<?php declare(strict_types=1);

namespace Rector\DeadCode\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Property;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\Maintainer\PropertyMaintainer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class RemoveUnusedPrivatePropertyRector extends AbstractRector
{
    /**
     * @var PropertyMaintainer
     */
    private $propertyMaintainer;

    public function __construct(PropertyMaintainer $propertyMaintainer)
    {
        $this->propertyMaintainer = $propertyMaintainer;
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
        if ($node->isPrivate() === false) {
            return null;
        }

        if (count($node->props) !== 1) {
            return null;
        }

        $propertyFetches = $this->propertyMaintainer->getAllPropertyFetch($node);

        // never used
        if ($propertyFetches === []) {
            $this->removeNode($node);
        }

        $uselessAssigns = $this->resolveUselessAssignNode($propertyFetches);

        if (count($uselessAssigns)) {
            $this->removeNode($node);
            foreach ($uselessAssigns as $uselessAssign) {
                $this->removeNode($uselessAssign);
            }
        }

        return $node;
    }

    /**
     * @param PropertyFetch[] $propertyFetches
     * @return Assign[]
     */
    private function resolveUselessAssignNode(array $propertyFetches): array
    {
        $uselessAssigns = [];

        foreach ($propertyFetches as $propertyFetch) {
            $propertyFetchParentNode = $propertyFetch->getAttribute(Attribute::PARENT_NODE);
            if ($propertyFetchParentNode instanceof Assign && $propertyFetchParentNode->var === $propertyFetch) {
                $uselessAssigns[] = $propertyFetchParentNode;
            } else {
                return [];
            }
        }

        return $uselessAssigns;
    }
}
