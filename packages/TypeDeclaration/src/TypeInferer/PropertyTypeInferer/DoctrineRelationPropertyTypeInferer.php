<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node\Stmt\Property;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\JoinColumnTagValueNode;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\ToManyTagNodeInterface;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\ToOneTagNodeInterface;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;

final class DoctrineRelationPropertyTypeInferer implements PropertyTypeInfererInterface
{
    /**
     * @var string
     */
    private const COLLECTION_TYPE = 'Doctrine\Common\Collections\Collection';

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    public function __construct(DocBlockManipulator $docBlockManipulator)
    {
        $this->docBlockManipulator = $docBlockManipulator;
    }

    /**
     * @return string[]
     */
    public function inferProperty(Property $property): array
    {
        if ($property->getDocComment() === null) {
            return [];
        }

        $phpDocInfo = $this->docBlockManipulator->createPhpDocInfoFromNode($property);
        $relationTagValueNode = $phpDocInfo->getDoctrineRelationTagValueNode();
        if ($relationTagValueNode === null) {
            return [];
        }

        if ($relationTagValueNode instanceof ToManyTagNodeInterface) {
            return $this->processToManyRelation($relationTagValueNode);
        } elseif ($relationTagValueNode instanceof ToOneTagNodeInterface) {
            $joinColumnTagValueNode = $phpDocInfo->getDoctrineJoinColumnTagValueNode();
            return $this->processToOneRelation($relationTagValueNode, $joinColumnTagValueNode);
        }

        return [];
    }

    public function getPriority(): int
    {
        return 900;
    }

    /**
     * @return string[]
     */
    private function processToManyRelation(ToManyTagNodeInterface $toManyTagNode): array
    {
        $types = [];

        $targetEntity = $toManyTagNode->getTargetEntity();
        if ($targetEntity) {
            $types[] = $targetEntity . '[]';
        }

        $types[] = self::COLLECTION_TYPE;

        return $types;
    }

    /**
     * @return string[]
     */
    private function processToOneRelation(
        ToOneTagNodeInterface $toOneTagNode,
        ?JoinColumnTagValueNode $joinColumnTagValueNode
    ): array {
        $types = [];

        $targetEntity = $toOneTagNode->getFqnTargetEntity();
        if ($targetEntity) {
            $types[] = $targetEntity;
        }

        // nullable by default
        if ($joinColumnTagValueNode === null || $joinColumnTagValueNode->isNullable()) {
            $types[] = 'null';
        }

        return $types;
    }
}
