<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\NodeFactory;

use PhpParser\Builder\Property as PropertyBuilder;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\SpacelessPhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\JoinColumnTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\ManyToOneTagValueNode;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\PhpParser\Node\Value\ValueResolver;

final class RelationPropertyFactory
{
    /**
     * @var ValueResolver
     */
    private $valueResolver;

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    public function __construct(ValueResolver $valueResolver, DocBlockManipulator $docBlockManipulator)
    {
        $this->valueResolver = $valueResolver;
        $this->docBlockManipulator = $docBlockManipulator;
    }

    /**
     * @return Property[]
     */
    public function createManyToOneProperties(Property $belongToProperty): array
    {
        $properties = [];

        $onlyPropertyDefault = $belongToProperty->props[0]->default;
        if ($onlyPropertyDefault === null) {
            throw new ShouldNotHappenException();
        }

        $belongsToValue = $this->valueResolver->getValue($onlyPropertyDefault);

        foreach ($belongsToValue as $propertyName => $manyToOneConfiguration) {
            $propertyName = lcfirst($propertyName);
            $propertyBuilder = new PropertyBuilder($propertyName);
            $propertyBuilder->makePrivate();

            $className = $manyToOneConfiguration['className'];

            // add @ORM\ManyToOne
            $manyToOneTagValueNode = new ManyToOneTagValueNode($className, null, null, null, null, $className);
            $propertyNode = $propertyBuilder->getNode();

            $spacelessPhpDocTagNode = new SpacelessPhpDocTagNode(
                ManyToOneTagValueNode::SHORT_NAME,
                $manyToOneTagValueNode
            );
            $this->docBlockManipulator->addTag($propertyNode, $spacelessPhpDocTagNode);

            $joinColumnTagValueNode = new JoinColumnTagValueNode($manyToOneConfiguration['foreignKey'], null);
            $spacelessPhpDocTagNode = new SpacelessPhpDocTagNode(
                JoinColumnTagValueNode::SHORT_NAME,
                $joinColumnTagValueNode
            );

            $this->docBlockManipulator->addTag($propertyNode, $spacelessPhpDocTagNode);

            $properties[] = $propertyNode;
        }

        return $properties;
    }
}
