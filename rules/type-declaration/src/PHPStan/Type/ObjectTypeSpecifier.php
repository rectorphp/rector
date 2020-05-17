<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\PHPStan\Type;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Use_;
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
        /** @var Use_[]|null $uses */
        $uses = $node->getAttribute(AttributeKey::USE_NODES);
        if ($uses === null) {
            return $objectType;
        }

        $aliasedObjectType = $this->matchAliasedObjectType($node, $objectType);

        if ($aliasedObjectType !== null) {
            return $aliasedObjectType;
        }

        $shortenedObjectType = $this->matchShortenedObjectType($node, $objectType);
        if ($shortenedObjectType !== null) {
            return $shortenedObjectType;
        }

        $sameNamespacedObjectType = $this->matchSameNamespacedObjectType($node, $objectType);
        if ($sameNamespacedObjectType !== null) {
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
        /** @var Use_[]|null $uses */
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
                $alias = $useUse->alias->toString();
                $fullyQualifiedName = $useUse->name->toString();

                // A. is alias in use statement matching this class alias
                if ($useUse->alias->toString() === $objectType->getClassName()) {
                    return new AliasedObjectType($alias, $fullyQualifiedName);
                }

                // B. is aliased classes matching the class name
                if ($useName === $objectType->getClassName()) {
                    return new AliasedObjectType($alias, $fullyQualifiedName);
                }
            }
        }

        return null;
    }

    private function matchShortenedObjectType(Node $node, ObjectType $objectType): ?ShortenedObjectType
    {
        /** @var Use_[]|null $uses */
        $uses = $node->getAttribute(AttributeKey::USE_NODES);
        if ($uses === null) {
            return null;
        }

        foreach ($uses as $use) {
            foreach ($use->uses as $useUse) {
                if ($useUse->alias !== null) {
                    continue;
                }

                $partialNamespaceObjectType = $this->matchPartialNamespaceObjectType($objectType, $useUse);
                if ($partialNamespaceObjectType !== null) {
                    return $partialNamespaceObjectType;
                }

                $partialNamespaceObjectType = $this->matchClassWithLastUseImportPart($objectType, $useUse);
                if ($partialNamespaceObjectType instanceof FullyQualifiedObjectType) {
                    return $partialNamespaceObjectType->getShortNameType();
                }

                if ($partialNamespaceObjectType instanceof ShortenedObjectType) {
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

        if ($objectType->getClassName() === $connectedClassName) {
            return null;
        }

        return new ShortenedObjectType($objectType->getClassName(), $connectedClassName);
    }

    /**
     * @return FullyQualifiedObjectType|ShortenedObjectType|null
     */
    private function matchClassWithLastUseImportPart(ObjectType $objectType, UseUse $useUse): ?ObjectType
    {
        if ($useUse->name->getLast() !== $objectType->getClassName()) {
            return null;
        }

        if (! ClassExistenceStaticHelper::doesClassLikeExist($useUse->name->toString())) {
            return null;
        }

        if ($objectType->getClassName() === $useUse->name->toString()) {
            return new FullyQualifiedObjectType($objectType->getClassName());
        }

        return new ShortenedObjectType($objectType->getClassName(), $useUse->name->toString());
    }
}
