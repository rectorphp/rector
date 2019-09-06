<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\PHPStan\Type;

use PhpParser\Node;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
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

        $shortednedObjectType = $this->matchShortenedObjectType($node, $objectType);
        if ($shortednedObjectType) {
            return $shortednedObjectType;
        }

        $sameNamespacedObjectType = $this->matchSameNamespacedObjectType($node, $objectType);
        if ($sameNamespacedObjectType) {
            return $sameNamespacedObjectType;
        }

        $className = ltrim($objectType->getClassName(), '\\');

        if ($this->isExistingClassLike($className)) {
            return new FullyQualifiedObjectType($className);
        }

        // invalid type
        return new MixedType();
    }

    private function isExistingClassLike(string $classLike): bool
    {
        return class_exists($classLike) || interface_exists($classLike) || trait_exists($classLike);
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

                if ($useUse->name->getLast() !== $objectType->getClassName()) {
                    continue;
                }

                if ($this->isExistingClassLike($useUse->name->toString())) {
                    return new ShortenedObjectType($objectType->getClassName(), $useUse->name->toString());
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

        if ($this->isExistingClassLike($namespacedObject)) {
            return new FullyQualifiedObjectType($namespacedObject);
        }

        return null;
    }
}
