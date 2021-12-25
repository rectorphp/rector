<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\PHPStan\Type;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StaticType;
use PHPStan\Type\StringType;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\NonExistingObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType;
use Rector\StaticTypeMapper\ValueObject\Type\SelfObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedGenericObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;

final class ObjectTypeSpecifier
{
    public function __construct(
        private readonly ReflectionProvider $reflectionProvider
    ) {
    }

    public function narrowToFullyQualifiedOrAliasedObjectType(
        Node $node,
        ObjectType $objectType,
        Scope|null $scope
    ): TypeWithClassName | UnionType | MixedType {
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

        if (ObjectReference::isValid($className)) {
            if (! $scope instanceof Scope) {
                throw new ShouldNotHappenException();
            }

            return $this->resolveObjectReferenceType($scope, $className);
        }

        if ($this->reflectionProvider->hasClass($className)) {
            return new FullyQualifiedObjectType($className);
        }

        if ($className === 'scalar') {
            // pseudo type, see https://www.php.net/manual/en/language.types.intro.php
            $scalarTypes = [new BooleanType(), new StringType(), new IntegerType(), new FloatType()];
            return new UnionType($scalarTypes);
        }

        // invalid type
        return new NonExistingObjectType($className);
    }

    private function matchAliasedObjectType(Node $node, ObjectType $objectType): ?AliasedObjectType
    {
        /** @var Use_[]|null $uses */
        $uses = $node->getAttribute(AttributeKey::USE_NODES);
        if ($uses === null) {
            return null;
        }

        $className = $objectType->getClassName();

        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);

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
                    $parent,
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

    private function matchShortenedObjectType(
        Node $node,
        ObjectType $objectType
    ): ShortenedObjectType|ShortenedGenericObjectType|null {
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
                    // keep Generic items
                    if ($objectType instanceof GenericObjectType) {
                        return new ShortenedGenericObjectType(
                            $objectType->getClassName(),
                            $objectType->getTypes(),
                            $partialNamespaceObjectType->getClassName()
                        );
                    }

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
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }

        $namespaceName = $scope->getNamespace();
        if ($namespaceName === null) {
            return null;
        }

        $namespacedObject = $namespaceName . '\\' . ltrim($objectType->getClassName(), '\\');

        if ($this->reflectionProvider->hasClass($namespacedObject)) {
            return new FullyQualifiedObjectType($namespacedObject);
        }

        return null;
    }

    private function matchPartialNamespaceObjectType(ObjectType $objectType, UseUse $useUse): ?ShortenedObjectType
    {
        // partial namespace
        if (! \str_starts_with($objectType->getClassName(), $useUse->name->getLast() . '\\')) {
            return null;
        }

        $classNameWithoutLastUsePart = Strings::after($objectType->getClassName(), '\\', 1);

        $connectedClassName = $useUse->name->toString() . '\\' . $classNameWithoutLastUsePart;
        if (! $this->reflectionProvider->hasClass($connectedClassName)) {
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

        if (! $this->reflectionProvider->hasClass($useUse->name->toString())) {
            return null;
        }

        if ($objectType->getClassName() === $useUse->name->toString()) {
            return new FullyQualifiedObjectType($objectType->getClassName());
        }

        return new ShortenedObjectType($objectType->getClassName(), $useUse->name->toString());
    }

    private function resolveObjectReferenceType(
        Scope $scope,
        string $classReferenceValue
    ): StaticType|SelfObjectType {
        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            throw new ShouldNotHappenException();
        }

        if (ObjectReference::STATIC()->getValue() === $classReferenceValue) {
            return new StaticType($classReflection);
        }

        if (ObjectReference::SELF()->getValue() === $classReferenceValue) {
            return new SelfObjectType($classReferenceValue, null, $classReflection);
        }

        if (ObjectReference::PARENT()->getValue() === $classReferenceValue) {
            $parentClassReflection = $classReflection->getParentClass();
            if (! $parentClassReflection instanceof ClassReflection) {
                throw new ShouldNotHappenException();
            }

            return new ParentStaticType($parentClassReflection);
        }

        throw new ShouldNotHappenException();
    }
}
