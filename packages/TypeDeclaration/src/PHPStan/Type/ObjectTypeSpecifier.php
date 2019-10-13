<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\PHPStan\Type;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\UseUse;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\AliasedObjectType;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\PHPStan\Type\ShortenedObjectType;

final class ObjectTypeSpecifier
{
    /**
     * @return AliasedObjectType|FullyQualifiedObjectType|ObjectType|MixedType
     */
    public function narrowToFullyQualifiedOrAlaisedObjectType(Node $node, ObjectType $objectType): Type
    {
        /** @var Node\Stmt\Use_[]|null $uses */
        $uses = $node->getAttribute(AttributeKey::USE_NODES);
        if ($uses === null) {
            return $objectType;
        }

        $aliasedObjectType = $this->matchAliasedObjectType($node, $objectType);
        if ($aliasedObjectType) {
            return $aliasedObjectType;
        }

        $shortenedObjectType = $this->matchShortenedObjectType($node, $objectType);
        if ($shortenedObjectType) {
            return $shortenedObjectType;
        }

        $sameNamespacedObjectType = $this->matchSameNamespacedObjectType($node, $objectType);
        if ($sameNamespacedObjectType) {
            return $sameNamespacedObjectType;
        }

        $className = ltrim($objectType->getClassName(), '\\');
        if (ClassExistenceStaticHelper::doesClassLikeExist($className)) {
            return new FullyQualifiedObjectType($className);
        }

        // invalid type
        return new MixedType();
    }

    private function matchAliasedObjectType(Node $node, ObjectType $objectType): ?AliasedObjectType
    {
        /** @var Node\Stmt\Use_[]|null $uses */
        $uses = $node->getAttribute(AttributeKey::USE_NODES);
        if ($uses === null) {
            return null;
        }

        foreach ($uses as $use) {
            foreach ($use->uses as $useUse) {
                if ($useUse->alias === null) {
                    continue;
                }

                $useName = $useUse->name->toString();
                if ($useName !== $objectType->getClassName()) {
                    continue;
                }

                return new AliasedObjectType($useUse->alias->toString());
            }
        }

        return null;
    }

    private function matchShortenedObjectType(Node $node, ObjectType $objectType): ?ShortenedObjectType
    {
        /** @var Node\Stmt\Use_[]|null $uses */
        $uses = $node->getAttribute(AttributeKey::USE_NODES);
        if ($uses === null) {
            return null;
        }

        foreach ($uses as $use) {
            foreach ($use->uses as $useUse) {
                if ($useUse->alias) {
                    continue;
                }

                $partialNamespaceObjectType = $this->matchPartialNamespaceObjectType($objectType, $useUse);
                if ($partialNamespaceObjectType) {
                    return $partialNamespaceObjectType;
                }

                $partialNamespaceObjectType = $this->matchClassWithLastUseImportPart($objectType, $useUse);
                if ($partialNamespaceObjectType) {
                    return $partialNamespaceObjectType;
                }
            }
        }

        return null;
    }

    private function matchSameNamespacedObjectType(Node $node, ObjectType $objectType): ?ObjectType
    {
        $namespaceName = $node->getAttribute(AttributeKey::NAMESPACE_NAME);
        if ($namespaceName === null) {
            return null;
        }

        $namespacedObject = $namespaceName . '\\' . $objectType->getClassName();

        if (ClassExistenceStaticHelper::doesClassLikeExist($namespacedObject)) {
            return new FullyQualifiedObjectType($namespacedObject);
        }

        return null;
    }

    private function matchPartialNamespaceObjectType(ObjectType $objectType, UseUse $useUse): ?ShortenedObjectType
    {
        // partial namespace
        if (! Strings::startsWith($objectType->getClassName(), $useUse->name->getLast() . '\\')) {
            return null;
        }

        $classNameWithoutLastUsePart = Strings::after($objectType->getClassName(), '\\', 1);

        $connectedClassName = $useUse->name->toString() . '\\' . $classNameWithoutLastUsePart;
        if (! ClassExistenceStaticHelper::doesClassLikeExist($connectedClassName)) {
            return null;
        }

        return new ShortenedObjectType($objectType->getClassName(), $connectedClassName);
    }

    private function matchClassWithLastUseImportPart(ObjectType $objectType, UseUse $useUse): ?ShortenedObjectType
    {
        if ($useUse->name->getLast() !== $objectType->getClassName()) {
            return null;
        }

        if (! ClassExistenceStaticHelper::doesClassLikeExist($useUse->name->toString())) {
            return null;
        }

        return new ShortenedObjectType($objectType->getClassName(), $useUse->name->toString());
    }
}
