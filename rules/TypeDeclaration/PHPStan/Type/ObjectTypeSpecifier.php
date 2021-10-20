<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\PHPStan\Type;

use RectorPrefix20211020\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\NonExistingObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedGenericObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
final class ObjectTypeSpecifier
{
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @return \PHPStan\Type\ObjectType|\Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType|\Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType|\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType|\PHPStan\Type\MixedType
     */
    public function narrowToFullyQualifiedOrAliasedObjectType(\PhpParser\Node $node, \PHPStan\Type\ObjectType $objectType)
    {
        /** @var Use_[]|null $uses */
        $uses = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::USE_NODES);
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
        $className = \ltrim($objectType->getClassName(), '\\');
        if ($this->reflectionProvider->hasClass($className)) {
            return new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($className);
        }
        // invalid type
        return new \Rector\StaticTypeMapper\ValueObject\Type\NonExistingObjectType($className);
    }
    private function matchAliasedObjectType(\PhpParser\Node $node, \PHPStan\Type\ObjectType $objectType) : ?\Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType
    {
        /** @var Use_[]|null $uses */
        $uses = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::USE_NODES);
        if ($uses === null) {
            return null;
        }
        $className = $objectType->getClassName();
        $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        foreach ($uses as $use) {
            foreach ($use->uses as $useUse) {
                if ($useUse->alias === null) {
                    continue;
                }
                $useName = $useUse->name->toString();
                $alias = $useUse->alias->toString();
                $fullyQualifiedName = $useUse->name->toString();
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
     * @return \Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType|\Rector\StaticTypeMapper\ValueObject\Type\ShortenedGenericObjectType|null
     */
    private function matchShortenedObjectType(\PhpParser\Node $node, \PHPStan\Type\ObjectType $objectType)
    {
        /** @var Use_[]|null $uses */
        $uses = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::USE_NODES);
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
    private function matchSameNamespacedObjectType(\PhpParser\Node $node, \PHPStan\Type\ObjectType $objectType) : ?\PHPStan\Type\ObjectType
    {
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        $namespaceName = $scope->getNamespace();
        if ($namespaceName === null) {
            return null;
        }
        $namespacedObject = $namespaceName . '\\' . \ltrim($objectType->getClassName(), '\\');
        if ($this->reflectionProvider->hasClass($namespacedObject)) {
            return new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($namespacedObject);
        }
        return null;
    }
    private function matchPartialNamespaceObjectType(\PHPStan\Type\ObjectType $objectType, \PhpParser\Node\Stmt\UseUse $useUse) : ?\Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType
    {
        // partial namespace
        if (\strncmp($objectType->getClassName(), $useUse->name->getLast() . '\\', \strlen($useUse->name->getLast() . '\\')) !== 0) {
            return null;
        }
        $classNameWithoutLastUsePart = \RectorPrefix20211020\Nette\Utils\Strings::after($objectType->getClassName(), '\\', 1);
        $connectedClassName = $useUse->name->toString() . '\\' . $classNameWithoutLastUsePart;
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
    private function matchClassWithLastUseImportPart(\PHPStan\Type\ObjectType $objectType, \PhpParser\Node\Stmt\UseUse $useUse) : ?\PHPStan\Type\ObjectType
    {
        if ($useUse->name->getLast() !== $objectType->getClassName()) {
            return null;
        }
        if (!$this->reflectionProvider->hasClass($useUse->name->toString())) {
            return null;
        }
        if ($objectType->getClassName() === $useUse->name->toString()) {
            return new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($objectType->getClassName());
        }
        return new \Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType($objectType->getClassName(), $useUse->name->toString());
    }
}
