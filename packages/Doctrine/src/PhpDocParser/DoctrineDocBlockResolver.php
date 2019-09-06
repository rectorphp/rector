<?php declare(strict_types=1);

namespace Rector\Doctrine\PhpDocParser;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Class_\EntityTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\ColumnTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\IdTagValueNode;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\DoctrineRelationTagValueNodeInterface;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;

final class DoctrineDocBlockResolver
{
    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    public function __construct(DocBlockManipulator $docBlockManipulator)
    {
        $this->docBlockManipulator = $docBlockManipulator;
    }

    public function isDoctrineEntityClass(Class_ $class): bool
    {
        $classPhpDocInfo = $this->getPhpDocInfo($class);
        if ($classPhpDocInfo === null) {
            return false;
        }

        return (bool) $classPhpDocInfo->getByType(EntityTagValueNode::class);
    }

    public function isDoctrineEntityClassWithIdProperty(Class_ $class): bool
    {
        if (! $this->isDoctrineEntityClass($class)) {
            return false;
        }

        foreach ($class->stmts as $classStmt) {
            if (! $classStmt instanceof Property) {
                continue;
            }

            if ($this->hasPropertyDoctrineIdTag($classStmt)) {
                return true;
            }
        }

        return false;
    }

    public function getTargetEntity(Property $property): ?string
    {
        $doctrineRelationTagValueNode = $this->getDoctrineRelationTagValueNode($property);
        if ($doctrineRelationTagValueNode === null) {
            return null;
        }

        return $doctrineRelationTagValueNode->getTargetEntity();
    }

    public function hasPropertyDoctrineIdTag(Property $property): bool
    {
        $propertyPhpDocInfo = $this->getPhpDocInfo($property);
        if ($propertyPhpDocInfo === null) {
            return false;
        }

        return (bool) $propertyPhpDocInfo->getByType(IdTagValueNode::class);
    }

    public function getDoctrineRelationTagValueNode(Property $property): ?DoctrineRelationTagValueNodeInterface
    {
        $propertyPhpDocInfo = $this->getPhpDocInfo($property);
        if ($propertyPhpDocInfo === null) {
            return null;
        }

        return $propertyPhpDocInfo->getByType(DoctrineRelationTagValueNodeInterface::class);
    }

    public function isDoctrineProperty(Property $property): bool
    {
        $propertyPhpDocInfo = $this->getPhpDocInfo($property);
        if ($propertyPhpDocInfo === null) {
            return false;
        }

        if ($propertyPhpDocInfo->getByType(ColumnTagValueNode::class)) {
            return true;
        }

        return (bool) $propertyPhpDocInfo->getByType(DoctrineRelationTagValueNodeInterface::class);
    }

    private function getPhpDocInfo(Node $node): ?PhpDocInfo
    {
        if ($node->getDocComment() === null) {
            return null;
        }

        return $this->docBlockManipulator->createPhpDocInfoFromNode($node);
    }
}
