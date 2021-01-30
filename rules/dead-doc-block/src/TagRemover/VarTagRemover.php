<?php

declare(strict_types=1);

namespace Rector\DeadDocBlock\TagRemover;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\Type\Type;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareArrayTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareUnionTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\PHPStanStaticTypeMapper\DoctrineTypeAnalyzer;
use Rector\StaticTypeMapper\StaticTypeMapper;

final class VarTagRemover
{
    /**
     * @var DoctrineTypeAnalyzer
     */
    private $doctrineTypeAnalyzer;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    public function __construct(
        DoctrineTypeAnalyzer $doctrineTypeAnalyzer,
        StaticTypeMapper $staticTypeMapper,
        PhpDocInfoFactory $phpDocInfoFactory
    ) {
        $this->doctrineTypeAnalyzer = $doctrineTypeAnalyzer;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }

    /**
     * @param Property|Param $node
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
     * @param Param|Property $node
     */
    private function isNonBasicArrayType(Node $node, VarTagValueNode $varTagValueNode): bool
    {
        if ($varTagValueNode->type instanceof AttributeAwareUnionTypeNode) {
            foreach ($varTagValueNode->type->types as $type) {
                if ($type instanceof AttributeAwareArrayTypeNode && $this->doesClassOrInterfaceExists($type)) {
                    return true;
                }
            }
        }

        if (! $this->isArrayTypeNode($varTagValueNode)) {
            return false;
        }

        $varTypeDocString = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPhpDocString(
            $varTagValueNode->type,
            $node
        );

        return $varTypeDocString !== 'array';
    }

    private function doesClassOrInterfaceExists(AttributeAwareArrayTypeNode $type): bool
    {
        if (class_exists((string) $type->type)) {
            return true;
        }

        if (interface_exists((string) $type->type)) {
            return true;
        }

        return false;
    }

    private function isArrayTypeNode(VarTagValueNode $varTagValueNode): bool
    {
        return $varTagValueNode->type instanceof ArrayTypeNode;
    }
}
