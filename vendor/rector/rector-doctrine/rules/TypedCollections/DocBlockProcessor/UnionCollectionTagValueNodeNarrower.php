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
            if ($hasNativeCollectionType) {
                $tagValueNode->type = $tagValueNode->type->type;
                $tagValueNode->setAttribute(PhpDocAttributeKey::ORIG_NODE, null);
                $collectionType = $tagValueNode->type;
                $this->addIntKeyIfMissing($collectionType);
                if ($collectionType->type instanceof IdentifierTypeNode && \substr_compare($collectionType->type->name, 'Collection', -\strlen('Collection')) !== 0) {
                    $collectionType->type = new FullyQualifiedIdentifierTypeNode(DoctrineClass::COLLECTION);
                }
                return \true;
            }
            return \false;
        }
        if (!$tagValueNode->type instanceof UnionTypeNode && !$tagValueNode->type instanceof IntersectionTypeNode) {
            return \false;
        }
        $hasChanged = \false;
        $hasCollectionType = \false;
        $hasArrayType = \false;
        $arrayTypeNode = null;
        $arrayKeyTypeNode = null;
        foreach ($tagValueNode->type->types as $key => $unionedTypeNode) {
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
            if ($unionedTypeNode instanceof IdentifierTypeNode && $unionedTypeNode->name === 'null' && $hasNativeCollectionType) {
                $hasChanged = \true;
                unset($tagValueNode->type->types[$key]);
                continue;
            }
            if ($unionedTypeNode instanceof IdentifierTypeNode && \in_array($unionedTypeNode->name, ['Collection', 'ArrayCollection'])) {
                if ($unionedTypeNode->name === 'ArrayCollection') {
                    $tagValueNode->type->types[$key] = new IdentifierTypeNode('\\' . DoctrineClass::COLLECTION);
                    $hasChanged = \true;
                }
                $hasCollectionType = \true;
            }
            // narrow array collection to more generic collection
            if ($unionedTypeNode instanceof GenericTypeNode && $unionedTypeNode->type->name === 'ArrayCollection') {
                $unionedTypeNode->type = new IdentifierTypeNode('\\' . DoctrineClass::COLLECTION);
                $hasChanged = \true;
            }
        }
        if (($hasArrayType === \false || $hasCollectionType === \false) && $hasChanged === \false) {
            return \false;
        }
        if ($arrayTypeNode instanceof TypeNode) {
            $tagValueNode->type = new GenericTypeNode(new IdentifierTypeNode('\\' . DoctrineClass::COLLECTION), [$arrayKeyTypeNode ?? new IdentifierTypeNode('int'), $arrayTypeNode]);
        } else {
            foreach ($tagValueNode->type->types as $key => $type) {
                if (!$type instanceof IdentifierTypeNode) {
                    continue;
                }
                if ($hasNativeCollectionType && $type->name === 'null') {
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
     * @param \PHPStan\PhpDocParser\Ast\Type\TypeNode|\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode $collectionType
     */
    private function addIntKeyIfMissing($collectionType) : void
    {
        if ($collectionType instanceof GenericTypeNode && \count($collectionType->genericTypes) === 1) {
            // add default key type
            $collectionType->genericTypes = \array_merge([new IdentifierTypeNode('int')], $collectionType->genericTypes);
        }
    }
}
