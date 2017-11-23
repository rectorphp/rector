<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\Property;
use Rector\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\ReflectionDocBlock\NodeAnalyzer\DocBlockAnalyzer;

final class ValueObjectRemoverRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $valueObjectsToSimpleTypes = [];

    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    /**
     * @param string[] $valueObjectsToSimpleTypes
     */
    public function __construct(array $valueObjectsToSimpleTypes, DocBlockAnalyzer $docBlockAnalyzer)
    {
        $this->valueObjectsToSimpleTypes = $valueObjectsToSimpleTypes;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        if ($node instanceof New_) {
            return $this->processNewCandidate($node);
        }

        if ($node instanceof Property) {
            return $this->processPropertyCandidate($node);
        }

        return false;
    }

    /**
     * @param New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof New_) {
            return $node->args[0];
        }

        if ($node instanceof Property) {
            return $this->refactorProperty($node);
        }

        return null;
    }

    private function processNewCandidate(New_ $newNode): bool
    {
        if (count($newNode->args) !== 1) {
            return false;
        }

        $classNodeTypes = $newNode->class->getAttribute(Attribute::TYPES);

        return (bool) array_intersect($classNodeTypes, $this->getValueObjects());
    }

    /**
     * @return string[]
     */
    private function getValueObjects(): array
    {
        return array_keys($this->valueObjectsToSimpleTypes);
    }

    private function processPropertyCandidate(Property $propertyNode): bool
    {
        $propertyNodeTypes = $propertyNode->getAttribute(Attribute::TYPES);

        return (bool) array_intersect($propertyNodeTypes, $this->getValueObjects());
    }

    private function refactorProperty(Property $propertyNode): Property
    {
        $newType = $this->matchPropertyTypeRemapping($propertyNode);
        if ($newType === null) {
            return $propertyNode;
        }

        $this->docBlockAnalyzer->replaceVarType($propertyNode, $newType);

        return $propertyNode;
    }

    private function matchPropertyTypeRemapping(Property $propertyNode): ?string
    {
        $propertyTypes = $propertyNode->getAttribute(Attribute::TYPES);
        foreach ($propertyTypes as $propertyType) {
            if (! isset($this->valueObjectsToSimpleTypes[$propertyType])) {
                continue;
            }

            return $this->valueObjectsToSimpleTypes[$propertyType];
        }

        return null;
    }
}
