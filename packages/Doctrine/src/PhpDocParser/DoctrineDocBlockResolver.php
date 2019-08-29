<?php declare(strict_types=1);

namespace Rector\Doctrine\PhpDocParser;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
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

        return (bool) $classPhpDocInfo->getDoctrineEntityTag();
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

        return (bool) $propertyPhpDocInfo->getDoctrineIdTagValueNode();
    }

    public function getDoctrineRelationTagValueNode(Property $property): ?DoctrineRelationTagValueNodeInterface
    {
        $propertyPhpDocInfo = $this->getPhpDocInfo($property);
        if ($propertyPhpDocInfo === null) {
            return null;
        }

        return $propertyPhpDocInfo->getDoctrineRelationTagValueNode();
    }

    private function getPhpDocInfo(Node $node): ?PhpDocInfo
    {
        if ($node->getDocComment() === null) {
            return null;
        }

        return $this->docBlockManipulator->createPhpDocInfoFromNode($node);
    }
}
