<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Rector\AbstractRector;

final class PropertyNameReplacerRector extends AbstractRector
{
    /**
     * class => [
     *     oldProperty => newProperty
     * ]
     *
     * @var string[][]
     */
    private $oldToNewPropertyByClass = [];

    /**
     * @var string|null
     */
    private $activeType;

    /**
     * @var PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;

    /**
     * @param string[][]
     */
    public function __construct(array $oldToNewPropertyByClass, PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->oldToNewPropertyByClass = $oldToNewPropertyByClass;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        $this->activeType = null;

        $matchedType = $this->propertyFetchAnalyzer->matchTypes($node, $this->getClasses());
        if ($matchedType) {
            $this->activeType = $matchedType;

            return true;
        }

        return false;
    }

    /**
     * @param PropertyFetch $propertyFetchNode
     */
    public function refactor(Node $propertyFetchNode): ?Node
    {
        $oldToNewProperties = $this->oldToNewPropertyByClass[$this->activeType];

        $propertyName = $propertyFetchNode->name->name;

        if (! isset($oldToNewProperties[$propertyName])) {
            return $propertyFetchNode;
        }

        foreach ($oldToNewProperties as $oldProperty => $newProperty) {
            if ($propertyName !== $oldProperty) {
                continue;
            }

            $propertyFetchNode->name->name = $newProperty;
        }

        return $propertyFetchNode;
    }

    /**
     * @return string[]
     */
    private function getClasses(): array
    {
        return array_keys($this->oldToNewPropertyByClass);
    }
}
