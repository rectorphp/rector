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
    private $perClassOldToNewProperties = [];

    /**
     * @var string[]
     */
    private $activeTypes = [];

    /**
     * @var PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;

    /**
     * @param string[][]
     */
    public function __construct(array $perClassOldToNewProperties, PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->perClassOldToNewProperties = $perClassOldToNewProperties;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        $this->activeTypes = [];

        $matchedTypes = $this->propertyFetchAnalyzer->matchTypes($node, $this->getClasses());
        if ($matchedTypes) {
            $this->activeTypes = $matchedTypes;

            return true;
        }

        return false;
    }

    /**
     * @param PropertyFetch $propertyFetchNode
     */
    public function refactor(Node $propertyFetchNode): ?Node
    {
        $oldToNewProperties = $this->matchOldToNewProperties();

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
        return array_keys($this->perClassOldToNewProperties);
    }

    /**
     * @return string[]
     */
    private function matchOldToNewProperties(): array
    {
        foreach ($this->activeTypes as $activeType) {
            if ($this->perClassOldToNewProperties[$activeType]) {
                return $this->perClassOldToNewProperties[$activeType];
            }
        }

        return [];
    }
}
