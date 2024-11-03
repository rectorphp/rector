<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\PHPStan;

use RectorPrefix202411\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
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
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\UseImportsResolver
     */
    private $useImportsResolver;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\Naming\NameScopeFactory
     */
    private $nameScopeFactory;
    public function __construct(ReflectionProvider $reflectionProvider, UseImportsResolver $useImportsResolver, NameScopeFactory $nameScopeFactory)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->useImportsResolver = $useImportsResolver;
        $this->nameScopeFactory = $nameScopeFactory;
    }
    /**
     * @return \PHPStan\Type\TypeWithClassName|\Rector\StaticTypeMapper\ValueObject\Type\NonExistingObjectType|\PHPStan\Type\UnionType|\PHPStan\Type\MixedType|\PHPStan\Type\Generic\TemplateType
     */
    public function narrowToFullyQualifiedOrAliasedObjectType(Node $node, ObjectType $objectType, ?\PHPStan\Analyser\Scope $scope)
    {
        $uses = $this->useImportsResolver->resolve();
        $aliasedObjectType = $this->matchAliasedObjectType($objectType, $uses);
        if ($aliasedObjectType instanceof AliasedObjectType) {
            return $aliasedObjectType;
        }
        $shortenedObjectType = $this->matchShortenedObjectType($objectType, $uses);
        if ($shortenedObjectType !== null) {
            return $shortenedObjectType;
        }
        $className = \ltrim($objectType->getClassName(), '\\');
        if ($this->reflectionProvider->hasClass($className)) {
            return new FullyQualifiedObjectType($className);
        }
        // probably in same namespace
        if ($scope instanceof Scope) {
            $namespaceName = $scope->getNamespace();
            if ($namespaceName !== null) {
                $newClassName = $namespaceName . '\\' . $className;
                if ($this->reflectionProvider->hasClass($newClassName)) {
                    return new FullyQualifiedObjectType($newClassName);
                }
            }
        }
        if ($scope instanceof Scope) {
            $classReflection = $scope->getClassReflection();
            if ($classReflection instanceof ClassReflection) {
                $templateTags = $classReflection->getTemplateTags();
                $nameScope = $this->nameScopeFactory->createNameScopeFromNodeWithoutTemplateTypes($node);
                $templateTypeScope = $nameScope->getTemplateTypeScope();
                if (!$templateTypeScope instanceof TemplateTypeScope) {
                    // invalid type
                    return new NonExistingObjectType($className);
                }
                $currentTemplateTag = $templateTags[$className] ?? null;
                if ($currentTemplateTag === null) {
                    // invalid type
                    return new NonExistingObjectType($className);
                }
                return TemplateTypeFactory::create($templateTypeScope, $currentTemplateTag->getName(), $currentTemplateTag->getBound(), $currentTemplateTag->getVariance());
            }
        }
        // invalid type
        return new NonExistingObjectType($className);
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
            return new AliasedObjectType($alias, $fullyQualifiedName);
        }
        // B. is aliased classes matching the class name
        if ($useName === $className) {
            return new AliasedObjectType($alias, $fullyQualifiedName);
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
    private function matchPartialNamespaceObjectType(string $prefix, ObjectType $objectType, UseUse $useUse) : ?ShortenedObjectType
    {
        // partial namespace
        if (\strncmp($objectType->getClassName(), $useUse->name->getLast() . '\\', \strlen($useUse->name->getLast() . '\\')) !== 0) {
            return null;
        }
        $classNameWithoutLastUsePart = Strings::after($objectType->getClassName(), '\\', 1);
        $connectedClassName = $prefix . $useUse->name->toString() . '\\' . $classNameWithoutLastUsePart;
        if (!$this->reflectionProvider->hasClass($connectedClassName)) {
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
    private function matchClassWithLastUseImportPart(string $prefix, ObjectType $objectType, UseUse $useUse) : ?ObjectType
    {
        if ($useUse->name->getLast() !== $objectType->getClassName()) {
            return null;
        }
        if (!$this->reflectionProvider->hasClass($prefix . $useUse->name->toString())) {
            return null;
        }
        if ($objectType->getClassName() === $prefix . $useUse->name->toString()) {
            return new FullyQualifiedObjectType($objectType->getClassName());
        }
        return new ShortenedObjectType($objectType->getClassName(), $prefix . $useUse->name->toString());
    }
}
