<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\DocBlockProcessor;

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\BetterPhpDocParser\ValueObject\Type\FullyQualifiedIdentifierTypeNode;
use Rector\Doctrine\Enum\DoctrineClass;
use Rector\PhpDocParser\PhpDocParser\ValueObject\PhpDocAttributeKey;
final class UnionCollectionTagValueNodeNarrower
{
    /**
     * @param \PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode $tagValueNode
     */
    public function narrow($tagValueNode, bool $hasNativeCollectionType = \false) : bool
    {
        if ($tagValueNode->type instanceof GenericTypeNode && $hasNativeCollectionType) {
            $identifierTypeNode = $tagValueNode->type->type;
            if ($identifierTypeNode->name === 'iterable') {
                $tagValueNode->type->type = new FullyQualifiedIdentifierTypeNode(DoctrineClass::COLLECTION);
                $this->addIntKeyIfMissing($tagValueNode->type);
                return \true;
            }
        }
        if ($tagValueNode->type instanceof NullableTypeNode) {
            return $this->processNullableTypeNode($hasNativeCollectionType, $tagValueNode);
        }
        return $this->processIterableAndUnionTypeNode($tagValueNode, $hasNativeCollectionType);
    }
    /**
     * @param \PHPStan\PhpDocParser\Ast\Type\TypeNode|\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode $collectionType
     */
    private function addIntKeyIfMissing($collectionType) : void
    {
        if (!$collectionType instanceof GenericTypeNode) {
            return;
        }
        if (\count($collectionType->genericTypes) !== 1) {
            return;
        }
        // add default key type
        $collectionType->genericTypes = \array_merge([new IdentifierTypeNode('int')], $collectionType->genericTypes);
    }
    /**
     * @param \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode $tagValueNode
     */
    private function processIterableAndUnionTypeNode($tagValueNode, bool $hasNativeCollectionType) : bool
    {
        if (!$tagValueNode->type instanceof UnionTypeNode && !$tagValueNode->type instanceof IntersectionTypeNode) {
            return \false;
        }
        $hasChanged = \false;
        $hasArrayType = \false;
        $arrayTypeNode = null;
        $arrayKeyTypeNode = null;
        // has collection docblock type?
        $hasCollectionType = $this->hasCollectionDocblockType($tagValueNode->type);
        $hasGenericIterableType = \false;
        $complexTypeNode = $tagValueNode->type;
        foreach ($complexTypeNode->types as $key => $unionedTypeNode) {
            // possibly array<key, value>
            if ($unionedTypeNode instanceof GenericTypeNode && $unionedTypeNode->type->name === 'array') {
                $hasArrayType = \true;
                // both key and value are known
                if (\count($unionedTypeNode->genericTypes) === 2) {
                    $arrayTypeNode = $unionedTypeNode->genericTypes[1];
                    $arrayKeyTypeNode = $unionedTypeNode->genericTypes[0];
                } elseif (\count($unionedTypeNode->genericTypes) === 1) {
                    $arrayTypeNode = $unionedTypeNode->genericTypes[0];
                }
                continue;
            }
            if ($unionedTypeNode instanceof ArrayTypeNode) {
                $hasArrayType = \true;
                $arrayTypeNode = $unionedTypeNode->type;
                continue;
            }
            // remove |null, if property type is present as Collection
            if ($unionedTypeNode instanceof IdentifierTypeNode) {
                if ($unionedTypeNode->name === 'null' && $hasNativeCollectionType) {
                    $hasChanged = \true;
                    unset($tagValueNode->type->types[$key]);
                    continue;
                }
                if ($unionedTypeNode->name === 'ArrayCollection') {
                    $tagValueNode->type->types[$key] = new IdentifierTypeNode('\\' . DoctrineClass::COLLECTION);
                    $hasChanged = \true;
                }
            }
            // narrow array collection to more generic collection
            if ($unionedTypeNode instanceof GenericTypeNode && \in_array($unionedTypeNode->type->name, ['ArrayCollection', 'iterable'], \true)) {
                $unionedTypeNode->type = new IdentifierTypeNode('\\' . DoctrineClass::COLLECTION);
                $hasChanged = \true;
                $hasGenericIterableType = \true;
            }
        }
        if (($hasArrayType === \false || $hasCollectionType === \false) && $hasChanged === \false) {
            return \false;
        }
        // remove duplicated Collection and Collection generics type
        if ($hasCollectionType && $hasGenericIterableType) {
            foreach ($complexTypeNode->types as $key => $singleType) {
                if ($this->isCollectionIdentifierTypeNode($singleType)) {
                    // remove as has generic iterable type already
                    unset($complexTypeNode->types[$key]);
                }
            }
        }
        if ($arrayTypeNode instanceof TypeNode) {
            $tagValueNode->type = new GenericTypeNode(new IdentifierTypeNode('\\' . DoctrineClass::COLLECTION), [$arrayKeyTypeNode ?? new IdentifierTypeNode('int'), $arrayTypeNode]);
        } else {
            foreach ($tagValueNode->type->types as $key => $type) {
                if (!$type instanceof IdentifierTypeNode) {
                    continue;
                }
                if ($hasNativeCollectionType && $type->name === 'null') {
                    // remove null type
                    unset($tagValueNode->type->types[$key]);
                    continue;
                }
                if ($type->name !== 'array') {
                    continue;
                }
                unset($tagValueNode->type->types[$key]);
            }
        }
        if ($tagValueNode->type instanceof UnionTypeNode && \count($tagValueNode->type->types) === 1) {
            // sole type
            $unionTypeNode = $tagValueNode->type;
            $tagValueNode->type = \array_pop($unionTypeNode->types);
            // refresh reprint
            $tagValueNode->setAttribute(PhpDocAttributeKey::ORIG_NODE, null);
        }
        return \true;
    }
    /**
     * @param \PHPStan\PhpDocParser\Ast\Type\UnionTypeNode|\PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode $complexTypeNode
     */
    private function hasCollectionDocblockType($complexTypeNode) : bool
    {
        foreach ($complexTypeNode->types as $singleType) {
            if ($this->isCollectionIdentifierTypeNode($singleType)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode $tagValueNode
     */
    private function processNullableTypeNode(bool $hasNativeCollectionType, $tagValueNode) : bool
    {
        if ($hasNativeCollectionType === \false) {
            return \false;
        }
        // unwrap nullable type
        $tagValueNode->type = $tagValueNode->type->type;
        // invoke reprint
        $tagValueNode->setAttribute(PhpDocAttributeKey::ORIG_NODE, null);
        $collectionType = $tagValueNode->type;
        $this->addIntKeyIfMissing($collectionType);
        if ($collectionType->type instanceof IdentifierTypeNode && \substr_compare($collectionType->type->name, 'Collection', -\strlen('Collection')) !== 0) {
            $collectionType->type = new FullyQualifiedIdentifierTypeNode(DoctrineClass::COLLECTION);
        }
        return \true;
    }
    private function isCollectionIdentifierTypeNode(TypeNode $typeNode) : bool
    {
        if (!$typeNode instanceof IdentifierTypeNode) {
            return \false;
        }
        return \in_array($typeNode->name, [DoctrineClass::COLLECTION, DoctrineClass::ARRAY_COLLECTION, 'Collection', 'ArrayCollection']);
    }
}
