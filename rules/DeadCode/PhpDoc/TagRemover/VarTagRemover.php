<?php

declare(strict_types=1);

namespace Rector\DeadCode\PhpDoc\TagRemover;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareArrayTypeNode;
use Rector\DeadCode\PhpDoc\DeadVarTagValueNodeAnalyzer;
use Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\PackageBuilder\Reflection\ClassLikeExistenceChecker;

final class VarTagRemover
{
    public function __construct(
        private DoctrineTypeAnalyzer $doctrineTypeAnalyzer,
        private StaticTypeMapper $staticTypeMapper,
        private PhpDocInfoFactory $phpDocInfoFactory,
        private ClassLikeExistenceChecker $classLikeExistenceChecker,
        private DeadVarTagValueNodeAnalyzer $deadVarTagValueNodeAnalyzer
    ) {
    }

    public function removeVarTagIfUseless(PhpDocInfo $phpDocInfo, Property $property): void
    {
        $varTagValueNode = $phpDocInfo->getVarTagValueNode();
        if (! $varTagValueNode instanceof VarTagValueNode) {
            return;
        }

        $isVarTagValueDead = $this->deadVarTagValueNodeAnalyzer->isDead($varTagValueNode, $property);
        if (! $isVarTagValueDead) {
            return;
        }

        $phpDocInfo->removeByType(VarTagValueNode::class);
    }

    /**
     * @param Expression|Property|Param $node
     */
    public function removeVarPhpTagValueNodeIfNotComment(Node $node, Type $type): void
    {
        // keep doctrine collection narrow type
        if ($this->doctrineTypeAnalyzer->isDoctrineCollectionWithIterableUnionType($type)) {
            return;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

        $varTagValueNode = $phpDocInfo->getVarTagValueNode();
        if (! $varTagValueNode instanceof VarTagValueNode) {
            return;
        }

        // has description? keep it
        if ($varTagValueNode->description !== '') {
            return;
        }

        // keep generic types
        if ($varTagValueNode->type instanceof GenericTypeNode) {
            return;
        }

        // keep string[] etc.
        if ($this->isNonBasicArrayType($node, $varTagValueNode)) {
            return;
        }

        $phpDocInfo->removeByType(VarTagValueNode::class);
    }

    /**
     * @param Expression|Param|Property $node
     */
    private function isNonBasicArrayType(Node $node, VarTagValueNode $varTagValueNode): bool
    {
        if ($varTagValueNode->type instanceof BracketsAwareUnionTypeNode) {
            foreach ($varTagValueNode->type->types as $type) {
                if ($type instanceof SpacingAwareArrayTypeNode && $this->isArrayOfExistingClassNode($node, $type)) {
                    return true;
                }
            }
        }

        if (! $this->isArrayTypeNode($varTagValueNode)) {
            return false;
        }

        return (string) $varTagValueNode->type !== 'array';
    }

    private function isArrayTypeNode(VarTagValueNode $varTagValueNode): bool
    {
        return $varTagValueNode->type instanceof ArrayTypeNode;
    }

    private function isArrayOfExistingClassNode(
        Node $node,
        SpacingAwareArrayTypeNode $spacingAwareArrayTypeNode
    ): bool {
        $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType(
            $spacingAwareArrayTypeNode,
            $node
        );

        if (! $staticType instanceof ArrayType) {
            return false;
        }

        $itemType = $staticType->getItemType();
        if (! $itemType instanceof ObjectType) {
            return false;
        }

        $className = $itemType->getClassName();

        return $this->classLikeExistenceChecker->doesClassLikeExist($className);
    }
}
