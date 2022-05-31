<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\PHPStan;

use RectorPrefix20220531\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\Naming\Naming\UseImportsResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\Naming\NameScopeFactory;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\NonExistingObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedGenericObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use Rector\TypeDeclaration\Contract\PHPStan\TypeWithClassTypeSpecifierInterface;
final class ObjectTypeSpecifier
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\UseImportsResolver
     */
    private $useImportsResolver;
    /**
     * @var TypeWithClassTypeSpecifierInterface[]
     * @readonly
     */
    private $typeWithClassTypeSpecifiers;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\Naming\NameScopeFactory
     */
    private $nameScopeFactory;
    /**
     * @param TypeWithClassTypeSpecifierInterface[] $typeWithClassTypeSpecifiers
     */
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\Naming\Naming\UseImportsResolver $useImportsResolver, array $typeWithClassTypeSpecifiers, \Rector\StaticTypeMapper\Naming\NameScopeFactory $nameScopeFactory)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->useImportsResolver = $useImportsResolver;
        $this->typeWithClassTypeSpecifiers = $typeWithClassTypeSpecifiers;
        $this->nameScopeFactory = $nameScopeFactory;
    }
    /**
     * @param \PHPStan\Analyser\Scope|null $scope
     * @return \PHPStan\Type\TypeWithClassName|\Rector\StaticTypeMapper\ValueObject\Type\NonExistingObjectType|\PHPStan\Type\UnionType|\PHPStan\Type\MixedType
     */
    public function narrowToFullyQualifiedOrAliasedObjectType(\PhpParser\Node $node, \PHPStan\Type\ObjectType $objectType, $scope)
    {
        $this->nameScopeFactory->createNameScopeFromNodeWithoutTemplateTypes($node);
        // @todo reuse name scope
        if ($scope instanceof \PHPStan\Analyser\Scope) {
            foreach ($this->typeWithClassTypeSpecifiers as $typeWithClassTypeSpecifier) {
                if ($typeWithClassTypeSpecifier->match($objectType, $scope)) {
                    return $typeWithClassTypeSpecifier->resolveObjectReferenceType($objectType, $scope);
                }
            }
        }
        $uses = $this->useImportsResolver->resolveForNode($node);
        if ($uses === []) {
            if (!$this->reflectionProvider->hasClass($objectType->getClassName())) {
                return new \Rector\StaticTypeMapper\ValueObject\Type\NonExistingObjectType($objectType->getClassName());
            }
            return new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($objectType->getClassName(), null, $objectType->getClassReflection());
        }
        $aliasedObjectType = $this->matchAliasedObjectType($node, $objectType, $uses);
        if ($aliasedObjectType !== null) {
            return $aliasedObjectType;
        }
        $shortenedObjectType = $this->matchShortenedObjectType($objectType, $uses);
        if ($shortenedObjectType !== null) {
            return $shortenedObjectType;
        }
        $className = \ltrim($objectType->getClassName(), '\\');
        if ($this->reflectionProvider->hasClass($className)) {
            return new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($className);
        }
        // invalid type
        return new \Rector\StaticTypeMapper\ValueObject\Type\NonExistingObjectType($className);
    }
    /**
     * @param Use_[]|GroupUse[] $uses
     */
    private function matchAliasedObjectType(\PhpParser\Node $node, \PHPStan\Type\ObjectType $objectType, array $uses) : ?\Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType
    {
        if ($uses === []) {
            return null;
        }
        $className = $objectType->getClassName();
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        foreach ($uses as $use) {
            $prefix = $use instanceof \PhpParser\Node\Stmt\GroupUse ? $use->prefix . '\\' : '';
            foreach ($use->uses as $useUse) {
                if ($useUse->alias === null) {
                    continue;
                }
                $useName = $prefix . $useUse->name->toString();
                $alias = $useUse->alias->toString();
                $fullyQualifiedName = $prefix . $useUse->name->toString();
                $processAliasedObject = $this->processAliasedObject($alias, $className, $useName, $parent, $fullyQualifiedName);
                if ($processAliasedObject instanceof \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType) {
                    return $processAliasedObject;
                }
            }
        }
        return null;
    }
    private function processAliasedObject(string $alias, string $className, string $useName, ?\PhpParser\Node $parentNode, string $fullyQualifiedName) : ?\Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType
    {
        // A. is alias in use statement matching this class alias
        if ($alias === $className) {
            return new \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType($alias, $fullyQualifiedName);
        }
        // B. is aliased classes matching the class name and parent node is MethodCall/StaticCall
        if ($useName === $className && ($parentNode instanceof \PhpParser\Node\Expr\MethodCall || $parentNode instanceof \PhpParser\Node\Expr\StaticCall)) {
            return new \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType($useName, $fullyQualifiedName);
        }
        // C. is aliased classes matching the class name
        if ($useName === $className) {
            return new \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType($alias, $fullyQualifiedName);
        }
        return null;
    }
    /**
     * @param Use_[]|GroupUse[] $uses
     * @return \Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType|\Rector\StaticTypeMapper\ValueObject\Type\ShortenedGenericObjectType|null
     */
    private function matchShortenedObjectType(\PHPStan\Type\ObjectType $objectType, array $uses)
    {
        if ($uses === []) {
            return null;
        }
        foreach ($uses as $use) {
            $prefix = $use instanceof \PhpParser\Node\Stmt\GroupUse ? $use->prefix . '\\' : '';
            foreach ($use->uses as $useUse) {
                if ($useUse->alias !== null) {
                    continue;
                }
                $partialNamespaceObjectType = $this->matchPartialNamespaceObjectType($prefix, $objectType, $useUse);
                if ($partialNamespaceObjectType !== null) {
                    return $partialNamespaceObjectType;
                }
                $partialNamespaceObjectType = $this->matchClassWithLastUseImportPart($prefix, $objectType, $useUse);
                if ($partialNamespaceObjectType instanceof \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType) {
                    // keep Generic items
                    if ($objectType instanceof \PHPStan\Type\Generic\GenericObjectType) {
                        return new \Rector\StaticTypeMapper\ValueObject\Type\ShortenedGenericObjectType($objectType->getClassName(), $objectType->getTypes(), $partialNamespaceObjectType->getClassName());
                    }
                    return $partialNamespaceObjectType->getShortNameType();
                }
                if ($partialNamespaceObjectType instanceof \Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType) {
                    return $partialNamespaceObjectType;
                }
            }
        }
        return null;
    }
    private function matchPartialNamespaceObjectType(string $prefix, \PHPStan\Type\ObjectType $objectType, \PhpParser\Node\Stmt\UseUse $useUse) : ?\Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType
    {
        // partial namespace
        if (\strncmp($objectType->getClassName(), $useUse->name->getLast() . '\\', \strlen($useUse->name->getLast() . '\\')) !== 0) {
            return null;
        }
        $classNameWithoutLastUsePart = \RectorPrefix20220531\Nette\Utils\Strings::after($objectType->getClassName(), '\\', 1);
        $connectedClassName = $prefix . $useUse->name->toString() . '\\' . $classNameWithoutLastUsePart;
        if (!$this->reflectionProvider->hasClass($connectedClassName)) {
            return null;
        }
        if ($objectType->getClassName() === $connectedClassName) {
            return null;
        }
        return new \Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType($objectType->getClassName(), $connectedClassName);
    }
    /**
     * @return FullyQualifiedObjectType|ShortenedObjectType|null
     */
    private function matchClassWithLastUseImportPart(string $prefix, \PHPStan\Type\ObjectType $objectType, \PhpParser\Node\Stmt\UseUse $useUse) : ?\PHPStan\Type\ObjectType
    {
        if ($useUse->name->getLast() !== $objectType->getClassName()) {
            return null;
        }
        if (!$this->reflectionProvider->hasClass($prefix . $useUse->name->toString())) {
            return null;
        }
        if ($objectType->getClassName() === $prefix . $useUse->name->toString()) {
            return new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($objectType->getClassName());
        }
        return new \Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType($objectType->getClassName(), $prefix . $useUse->name->toString());
    }
}
