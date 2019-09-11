<?php declare(strict_types=1);

namespace Rector\DeadCode\Doctrine;

use Doctrine\ORM\Mapping\Entity;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Class_\EntityTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Class_\InheritanceTypeTagValueNode;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\DoctrineRelationTagValueNodeInterface;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\InversedByNodeInterface;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\MappedByNodeInterface;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class DoctrineEntityManipulator
{
    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(DocBlockManipulator $docBlockManipulator, NameResolver $nameResolver)
    {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->nameResolver = $nameResolver;
    }

    public function resolveOtherProperty(Property $property): ?string
    {
        if ($property->getDocComment() === null) {
            return null;
        }

        $phpDocInfo = $this->docBlockManipulator->createPhpDocInfoFromNode($property);

        $relationTagValueNode = $phpDocInfo->getByType(DoctrineRelationTagValueNodeInterface::class);
        if ($relationTagValueNode === null) {
            return null;
        }

        $otherProperty = null;
        if ($relationTagValueNode instanceof MappedByNodeInterface) {
            $otherProperty = $relationTagValueNode->getMappedBy();
        }

        if ($otherProperty !== null) {
            return $otherProperty;
        }

        if ($relationTagValueNode instanceof InversedByNodeInterface) {
            return $relationTagValueNode->getInversedBy();
        }

        return null;
    }

    public function isNonAbstractDoctrineEntityClass(Class_ $class): bool
    {
        if ($class->isAnonymous()) {
            return false;
        }

        if ($class->isAbstract()) {
            return false;
        }

        // is parent entity
        if ($this->docBlockManipulator->hasTag($class, InheritanceTypeTagValueNode::class)) {
            return false;
        }

        return $this->docBlockManipulator->hasTag($class, EntityTagValueNode::class);
    }

    public function removeMappedByOrInversedByFromProperty(Property $property): void
    {
        $doc = $property->getDocComment();
        if ($doc === null) {
            return;
        }

        $phpDocInfo = $this->docBlockManipulator->createPhpDocInfoFromNode($property);
        $relationTagValueNode = $phpDocInfo->getByType(DoctrineRelationTagValueNodeInterface::class);

        $shouldUpdate = false;
        if ($relationTagValueNode instanceof MappedByNodeInterface) {
            if ($relationTagValueNode->getMappedBy()) {
                $shouldUpdate = true;
                $relationTagValueNode->removeMappedBy();
            }
        }

        if ($relationTagValueNode instanceof InversedByNodeInterface) {
            if ($relationTagValueNode->getInversedBy()) {
                $shouldUpdate = true;
                $relationTagValueNode->removeInversedBy();
            }
        }

        if ($shouldUpdate === false) {
            return;
        }

        $this->docBlockManipulator->updateNodeWithPhpDocInfo($property, $phpDocInfo);
    }

    /**
     * @return string[]
     */
    public function resolveRelationPropertyNames(Class_ $class): array
    {
        $manyToOnePropertyNames = [];

        foreach ($class->getProperties() as $property) {
            if ($property->getDocComment() === null) {
                continue;
            }

            $phpDocInfo = $this->docBlockManipulator->createPhpDocInfoFromNode($property);
            if ($phpDocInfo->getByType(DoctrineRelationTagValueNodeInterface::class) === null) {
                continue;
            }

            $manyToOnePropertyNames[] = $this->nameResolver->getName($property);
        }

        return $manyToOnePropertyNames;
    }
}
