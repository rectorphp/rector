<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\RectorDefinition;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;
use Rector\TypeDeclaration\ValueObject\IdentifierValueObject;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 */
final class PropertyTypeDeclarationRector extends AbstractRector
{
    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var PropertyTypeInferer
     */
    private $propertyTypeInferer;

    public function __construct(DocBlockManipulator $docBlockManipulator, PropertyTypeInferer $propertyTypeInferer)
    {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->propertyTypeInferer = $propertyTypeInferer;
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

        $types = $this->propertyTypeInferer->inferProperty($node);
        if ($types) {
            $this->setNodeVarTypes($node, $types);
            return $node;
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
}
