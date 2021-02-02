<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\PHPStan\Type;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;

final class ObjectTypeSpecifier
{
    /**
     * @return AliasedObjectType|FullyQualifiedObjectType|ObjectType|MixedType
     */
    public function narrowToFullyQualifiedOrAliasedObjectType(Node $node, ObjectType $objectType): Type
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

        $className = $objectType->getClassName();
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        foreach ($uses as $use) {
            foreach ($use->uses as $useUse) {
                if ($useUse->alias === null) {
                    continue;
                }

                $useName = $useUse->name->toString();
                $alias = $useUse->alias->toString();
                $fullyQualifiedName = $useUse->name->toString();

                $processAliasedObject = $this->processAliasedObject(
                    $alias,
                    $className,
                    $useName,
                    $parentNode,
                    $fullyQualifiedName
                );
                if ($processAliasedObject instanceof AliasedObjectType) {
                    return $processAliasedObject;
                }
            }
        }

        return null;
    }

    private function processAliasedObject(
        string $alias,
        string $className,
        string $useName,
        ?Node $parentNode,
        string $fullyQualifiedName
    ): ?AliasedObjectType {
        // A. is alias in use statement matching this class alias
        if ($alias === $className) {
            return new AliasedObjectType($alias, $fullyQualifiedName);
        }

        // B. is aliased classes matching the class name and parent node is MethodCall/StaticCall
        if ($useName === $className && ($parentNode instanceof MethodCall || $parentNode instanceof StaticCall)) {
            return new AliasedObjectType($useName, $fullyQualifiedName);
        }

        // C. is aliased classes matching the class name
        if ($useName === $className) {
            return new AliasedObjectType($alias, $fullyQualifiedName);
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

        $namespacedObject = $namespaceName . '\\' . ltrim($objectType->getClassName(), '\\');

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
