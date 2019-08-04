<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\RectorDefinition;
use Rector\TypeDeclaration\Contract\PropertyTypeInfererInterface;
use Rector\TypeDeclaration\Exception\ConflictingPriorityException;
use Rector\TypeDeclaration\ValueObject\IdentifierValueObject;

final class PropertyTypeDeclarationRector extends AbstractRector
{
    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var PropertyTypeInfererInterface[]
     */
    private $propertyTypeInferers = [];

    /**
     * @param PropertyTypeInfererInterface[] $propertyTypeInferers
     */
    public function __construct(DocBlockManipulator $docBlockManipulator, array $propertyTypeInferers = [])
    {
        $this->docBlockManipulator = $docBlockManipulator;

        $this->sortAndSetPropertyTypeInferers($propertyTypeInferers);
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add missing @var to properties that are missing it');
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
        if (count($node->props) !== 1) {
            return null;
        }

        if ($this->docBlockManipulator->hasTag($node, '@var')) {
            return null;
        }

        foreach ($this->propertyTypeInferers as $propertyTypeInferer) {
            $types = $propertyTypeInferer->inferProperty($node);
            if ($types) {
                $this->setNodeVarTypes($node, $types);
                return $node;
            }
        }

        return null;
    }

    /**
     * @param string[]|IdentifierValueObject[] $varTypes
     */
    private function setNodeVarTypes(Node $node, array $varTypes): Node
    {
        $this->docBlockManipulator->changeVarTag($node, $varTypes);

        return $node;
    }

    /**
     * @param PropertyTypeInfererInterface[] $propertyTypeInferers
     */
    private function sortAndSetPropertyTypeInferers(array $propertyTypeInferers): void
    {
        foreach ($propertyTypeInferers as $propertyTypeInferer) {
            $this->ensurePriorityIsUnique($propertyTypeInferer);
            $this->propertyTypeInferers[$propertyTypeInferer->getPriority()] = $propertyTypeInferer;
        }

        krsort($this->propertyTypeInferers);
    }

    private function ensurePriorityIsUnique(PropertyTypeInfererInterface $propertyTypeInferer): void
    {
        if (! isset($this->propertyTypeInferers[$propertyTypeInferer->getPriority()])) {
            return;
        }

        $alreadySetPropertyTypeInferer = $this->propertyTypeInferers[$propertyTypeInferer->getPriority()];

        throw new ConflictingPriorityException($propertyTypeInferer, $alreadySetPropertyTypeInferer);
    }
}
