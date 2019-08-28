<?php declare(strict_types=1);

namespace Rector\Doctrine\AbstarctRector;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\DoctrineRelationTagValueNodeInterface;
use Rector\Rector\AbstractRector\DocBlockManipulatorTrait;

trait DoctrineTrait
{
    use DocBlockManipulatorTrait;

    protected function isDoctrineEntityClass(Class_ $class): bool
    {
        $classPhpDocInfo = $this->getPhpDocInfo($class);
        if ($classPhpDocInfo === null) {
            return false;
        }

        return (bool) $classPhpDocInfo->getDoctrineEntityTag();
    }

    protected function getTargetEntity(Property $property): ?string
    {
        $doctrineRelationTagValueNode = $this->getDoctrineRelationTagValueNode($property);
        if ($doctrineRelationTagValueNode === null) {
            return null;
        }

        return $doctrineRelationTagValueNode->getTargetEntity();
    }

    protected function getDoctrineRelationTagValueNode(Property $property): ?DoctrineRelationTagValueNodeInterface
    {
        $propertyPhpDocInfo = $this->getPhpDocInfo($property);
        if ($propertyPhpDocInfo === null) {
            return null;
        }

        return $propertyPhpDocInfo->getDoctrineRelationTagValueNode();
    }
}
