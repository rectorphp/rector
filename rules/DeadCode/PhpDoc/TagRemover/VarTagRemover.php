<?php

declare (strict_types=1);
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
final class VarTagRemover
{
    /**
     * @var \Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer
     */
    private $doctrineTypeAnalyzer;
    /**
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @var \Rector\DeadCode\PhpDoc\DeadVarTagValueNodeAnalyzer
     */
    private $deadVarTagValueNodeAnalyzer;
    public function __construct(\Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer $doctrineTypeAnalyzer, \Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \Rector\DeadCode\PhpDoc\DeadVarTagValueNodeAnalyzer $deadVarTagValueNodeAnalyzer)
    {
        $this->doctrineTypeAnalyzer = $doctrineTypeAnalyzer;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->deadVarTagValueNodeAnalyzer = $deadVarTagValueNodeAnalyzer;
    }
    public function removeVarTagIfUseless(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, \PhpParser\Node\Stmt\Property $property) : void
    {
        $varTagValueNode = $phpDocInfo->getVarTagValueNode();
        if (!$varTagValueNode instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode) {
            return;
        }
        $isVarTagValueDead = $this->deadVarTagValueNodeAnalyzer->isDead($varTagValueNode, $property);
        if (!$isVarTagValueDead) {
            return;
        }
        $phpDocInfo->removeByType(\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode::class);
    }
    /**
     * @param \PhpParser\Node\Stmt\Expression|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $node
     */
    public function removeVarPhpTagValueNodeIfNotComment($node, \PHPStan\Type\Type $type) : void
    {
        // keep doctrine collection narrow type
        if ($this->doctrineTypeAnalyzer->isDoctrineCollectionWithIterableUnionType($type)) {
            return;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $varTagValueNode = $phpDocInfo->getVarTagValueNode();
        if (!$varTagValueNode instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode) {
            return;
        }
        // has description? keep it
        if ($varTagValueNode->description !== '') {
            return;
        }
        // keep generic types
        if ($varTagValueNode->type instanceof \PHPStan\PhpDocParser\Ast\Type\GenericTypeNode) {
            return;
        }
        // keep string[] etc.
        if ($this->isNonBasicArrayType($node, $varTagValueNode)) {
            return;
        }
        $phpDocInfo->removeByType(\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode::class);
    }
    /**
     * @param \PhpParser\Node\Stmt\Expression|\PhpParser\Node\Param|\PhpParser\Node\Stmt\Property $node
     */
    private function isNonBasicArrayType($node, \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode $varTagValueNode) : bool
    {
        if ($varTagValueNode->type instanceof \Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode) {
            foreach ($varTagValueNode->type->types as $type) {
                if ($type instanceof \Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareArrayTypeNode && $this->isArrayOfClass($node, $type)) {
                    return \true;
                }
                // keep generic types
                if ($type instanceof \PHPStan\PhpDocParser\Ast\Type\GenericTypeNode) {
                    return \true;
                }
            }
        }
        if (!$this->isArrayTypeNode($varTagValueNode)) {
            return \false;
        }
        return (string) $varTagValueNode->type !== 'array';
    }
    private function isArrayTypeNode(\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode $varTagValueNode) : bool
    {
        return $varTagValueNode->type instanceof \PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
    }
    private function isArrayOfClass(\PhpParser\Node $node, \Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareArrayTypeNode $spacingAwareArrayTypeNode) : bool
    {
        if ($spacingAwareArrayTypeNode->type instanceof \Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareArrayTypeNode) {
            return $this->isArrayOfClass($node, $spacingAwareArrayTypeNode->type);
        }
        $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($spacingAwareArrayTypeNode, $node);
        if (!$staticType instanceof \PHPStan\Type\ArrayType) {
            return \false;
        }
        $itemType = $staticType->getItemType();
        return $itemType instanceof \PHPStan\Type\ObjectType;
    }
}
