<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\PHPStan;

use RectorPrefix202506\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\UseItem;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Generic\TemplateType;
use PHPStan\Type\Generic\TemplateTypeFactory;
use PHPStan\Type\Generic\TemplateTypeScope;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\Naming\Naming\UseImportsResolver;
use Rector\StaticTypeMapper\Naming\NameScopeFactory;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\NonExistingObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedGenericObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
final class ObjectTypeSpecifier
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     */
    private UseImportsResolver $useImportsResolver;
    /**
     * @readonly
     */
    private NameScopeFactory $nameScopeFactory;
    public function __construct(ReflectionProvider $reflectionProvider, UseImportsResolver $useImportsResolver, NameScopeFactory $nameScopeFactory)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->useImportsResolver = $useImportsResolver;
        $this->nameScopeFactory = $nameScopeFactory;
    }
    /**
     * @return \PHPStan\Type\TypeWithClassName|\Rector\StaticTypeMapper\ValueObject\Type\NonExistingObjectType|\PHPStan\Type\UnionType|\PHPStan\Type\MixedType|\PHPStan\Type\Generic\TemplateType
     */
    public function narrowToFullyQualifiedOrAliasedObjectType(Node $node, ObjectType $objectType, ?\PHPStan\Analyser\Scope $scope, bool $withPreslash = \false)
    {
        $className = \ltrim($objectType->getClassName(), '\\');
        if (\strncmp($objectType->getClassName(), '\\', \strlen('\\')) === 0) {
            return new FullyQualifiedObjectType($className);
        }
        $uses = $this->useImportsResolver->resolve();
        $aliasedObjectType = $this->matchAliasedObjectType($objectType, $uses);
        if ($aliasedObjectType instanceof AliasedObjectType) {
            return $aliasedObjectType;
        }
        $shortenedObjectType = $this->matchShortenedObjectType($objectType, $uses);
        if ($shortenedObjectType !== null) {
            return $shortenedObjectType;
        }
        if ($this->reflectionProvider->hasClass($className)) {
            return new FullyQualifiedObjectType($className);
        }
        // probably in same namespace
        $namespaceName = null;
        if ($scope instanceof Scope) {
            $namespaceName = $scope->getNamespace();
            if ($namespaceName !== null) {
                $newClassName = $namespaceName . '\\' . $className;
                if ($this->reflectionProvider->hasClass($newClassName)) {
                    return new FullyQualifiedObjectType($newClassName);
                }
            }
            $classReflection = $scope->getClassReflection();
            if ($classReflection instanceof ClassReflection) {
                $templateTags = $classReflection->getTemplateTags();
                $nameScope = $this->nameScopeFactory->createNameScopeFromNodeWithoutTemplateTypes($node);
                $templateTypeScope = $nameScope->getTemplateTypeScope();
                if (!$templateTypeScope instanceof TemplateTypeScope) {
                    // invalid type
                    return $this->resolveNamespacedNonExistingObjectType($namespaceName, $className, $withPreslash);
                }
                $currentTemplateTag = $templateTags[$className] ?? null;
                if ($currentTemplateTag === null) {
                    // invalid type
                    return $this->resolveNamespacedNonExistingObjectType($namespaceName, $className, $withPreslash);
                }
                return TemplateTypeFactory::create($templateTypeScope, $currentTemplateTag->getName(), $currentTemplateTag->getBound(), $currentTemplateTag->getVariance());
            }
        }
        // invalid type
        return $this->resolveNamespacedNonExistingObjectType($namespaceName, $className, $withPreslash);
    }
    private function resolveNamespacedNonExistingObjectType(?string $namespacedName, string $className, bool $withPreslash) : NonExistingObjectType
    {
        if ($namespacedName === null) {
            return new NonExistingObjectType($className);
        }
        if ($withPreslash) {
            return new NonExistingObjectType($className);
        }
        if (\strpos($className, '\\') !== \false) {
            return new NonExistingObjectType($className);
        }
        return new NonExistingObjectType($namespacedName . '\\' . $className);
    }
    /**
     * @param array<Use_|GroupUse> $uses
     */
    private function matchAliasedObjectType(ObjectType $objectType, array $uses) : ?AliasedObjectType
    {
        if ($uses === []) {
            return null;
        }
        $className = $objectType->getClassName();
        foreach ($uses as $use) {
            $prefix = $this->useImportsResolver->resolvePrefix($use);
            foreach ($use->uses as $useUse) {
                if (!$useUse->alias instanceof Identifier) {
                    continue;
                }
                $useName = $prefix . $useUse->name->toString();
                $alias = $useUse->alias->toString();
                $fullyQualifiedName = $prefix . $useUse->name->toString();
                $processAliasedObject = $this->processAliasedObject($alias, $className, $useName, $fullyQualifiedName);
                if ($processAliasedObject instanceof AliasedObjectType) {
                    return $processAliasedObject;
                }
            }
        }
        return null;
    }
    private function processAliasedObject(string $alias, string $className, string $useName, string $fullyQualifiedName) : ?AliasedObjectType
    {
        // A. is alias in use statement matching this class alias
        if ($alias === $className) {
            return new AliasedObjectType($className, $fullyQualifiedName);
        }
        // B. is aliased classes matching the class name
        if ($useName === $className) {
            return new AliasedObjectType($className, $fullyQualifiedName);
        }
        if (\strncmp($className, $alias . '\\', \strlen($alias . '\\')) === 0) {
            return new AliasedObjectType($className, $fullyQualifiedName . \ltrim($className, $alias));
        }
        return null;
    }
    /**
     * @param array<Use_|GroupUse> $uses
     * @return \Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType|\Rector\StaticTypeMapper\ValueObject\Type\ShortenedGenericObjectType|null
     */
    private function matchShortenedObjectType(ObjectType $objectType, array $uses)
    {
        if ($uses === []) {
            return null;
        }
        foreach ($uses as $use) {
            $prefix = $use instanceof GroupUse ? $use->prefix . '\\' : '';
            foreach ($use->uses as $useUse) {
                if ($useUse->alias instanceof Identifier) {
                    continue;
                }
                $partialNamespaceObjectType = $this->matchPartialNamespaceObjectType($prefix, $objectType, $useUse);
                if ($partialNamespaceObjectType instanceof ShortenedObjectType) {
                    return $partialNamespaceObjectType;
                }
                $partialNamespaceObjectType = $this->matchClassWithLastUseImportPart($prefix, $objectType, $useUse);
                if ($partialNamespaceObjectType instanceof FullyQualifiedObjectType) {
                    // keep Generic items
                    if ($objectType instanceof GenericObjectType) {
                        return new ShortenedGenericObjectType($objectType->getClassName(), $objectType->getTypes(), $partialNamespaceObjectType->getClassName());
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
    private function matchPartialNamespaceObjectType(string $prefix, ObjectType $objectType, UseItem $useItem) : ?ShortenedObjectType
    {
        if ($objectType->getClassName() === $useItem->name->getLast()) {
            return new ShortenedObjectType($objectType->getClassName(), $prefix . $useItem->name->toString());
        }
        // partial namespace
        if (\strncmp($objectType->getClassName(), $useItem->name->getLast() . '\\', \strlen($useItem->name->getLast() . '\\')) !== 0) {
            return null;
        }
        $classNameWithoutLastUsePart = Strings::after($objectType->getClassName(), '\\', 1);
        $connectedClassName = $prefix . $useItem->name->toString() . '\\' . $classNameWithoutLastUsePart;
        if ($objectType->getClassName() === $connectedClassName) {
            return null;
        }
        return new ShortenedObjectType($objectType->getClassName(), $connectedClassName);
    }
    /**
     * @return FullyQualifiedObjectType|ShortenedObjectType|null
     */
    private function matchClassWithLastUseImportPart(string $prefix, ObjectType $objectType, UseItem $useItem) : ?ObjectType
    {
        if ($useItem->name->getLast() !== $objectType->getClassName()) {
            return null;
        }
        if (!$this->reflectionProvider->hasClass($prefix . $useItem->name->toString())) {
            return null;
        }
        if ($objectType->getClassName() === $prefix . $useItem->name->toString()) {
            return new FullyQualifiedObjectType($objectType->getClassName());
        }
        return new ShortenedObjectType($objectType->getClassName(), $prefix . $useItem->name->toString());
    }
}
