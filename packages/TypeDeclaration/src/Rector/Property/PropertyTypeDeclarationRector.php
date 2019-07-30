<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\RectorDefinition;
use Rector\TypeDeclaration\Contract\PropertyTypeInfererInterface;

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
        $this->propertyTypeInferers = $propertyTypeInferers;
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
     * @param string[] $varTypes
     */
    private function setNodeVarTypes(Node $node, array $varTypes): Node
    {
        $typesAsString = implode('|', $varTypes);

        $this->docBlockManipulator->changeVarTag($node, $typesAsString);

        return $node;
    }
}
