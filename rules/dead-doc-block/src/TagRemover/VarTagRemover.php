<?php

declare(strict_types=1);

namespace Rector\DeadDocBlock\TagRemover;

use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareArrayTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareUnionTypeNode;
use Rector\NodeTypeResolver\Node\AttributeKey;
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

    public function __construct(DoctrineTypeAnalyzer $doctrineTypeAnalyzer, StaticTypeMapper $staticTypeMapper)
    {
        $this->doctrineTypeAnalyzer = $doctrineTypeAnalyzer;
        $this->staticTypeMapper = $staticTypeMapper;
    }

    public function removeVarPhpTagValueNodeIfNotComment(Property $property, \PHPStan\Type\Type $type): void
    {
        // keep doctrine collection narrow type
        if ($this->doctrineTypeAnalyzer->isDoctrineCollectionWithIterableUnionType($type)) {
            return;
        }

        $propertyPhpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        // nothing to remove
        if ($propertyPhpDocInfo === null) {
            return;
        }

        $varTagValueNode = $propertyPhpDocInfo->getByType(VarTagValueNode::class);
        if ($varTagValueNode === null) {
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
        if ($this->isNonBasicArrayType($property, $varTagValueNode)) {
            return;
        }

        $propertyPhpDocInfo->removeByType(VarTagValueNode::class);
    }

    private function isNonBasicArrayType(Property $property, VarTagValueNode $varTagValueNode): bool
    {
        if ($varTagValueNode->type instanceof AttributeAwareUnionTypeNode) {
            foreach ($varTagValueNode->type->types as $type) {
                if ($type instanceof AttributeAwareArrayTypeNode && class_exists((string) $type->type)) {
                    return true;
                }
            }
        }

        if (! $this->isArrayTypeNode($varTagValueNode)) {
            return false;
        }

        $varTypeDocString = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPhpDocString(
            $varTagValueNode->type,
            $property
        );

        return $varTypeDocString !== 'array';
    }

    private function isArrayTypeNode(VarTagValueNode $varTagValueNode): bool
    {
        return $varTagValueNode->type instanceof ArrayTypeNode;
    }
}
