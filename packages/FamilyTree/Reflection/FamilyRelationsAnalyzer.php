<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\FamilyTree\Reflection;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\ComplexType;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticPropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\Stmt;
use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Interface_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Property;
use RectorPrefix20220606\PHPStan\Analyser\Scope;
use RectorPrefix20220606\PHPStan\Reflection\ClassReflection;
use RectorPrefix20220606\PHPStan\Reflection\ReflectionProvider;
use RectorPrefix20220606\PHPStan\Type\NullType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPStan\Type\UnionType;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20220606\Rector\Core\PhpParser\AstResolver;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\BetterNodeFinder;
use RectorPrefix20220606\Rector\FamilyTree\ValueObject\PropertyType;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use RectorPrefix20220606\Rector\StaticTypeMapper\StaticTypeMapper;
use RectorPrefix20220606\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
final class FamilyRelationsAnalyzer
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Symplify\PackageBuilder\Reflection\PrivatesAccessor
     */
    private $privatesAccessor;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    public function __construct(ReflectionProvider $reflectionProvider, PrivatesAccessor $privatesAccessor, NodeNameResolver $nodeNameResolver, BetterNodeFinder $betterNodeFinder, StaticTypeMapper $staticTypeMapper, AstResolver $astResolver)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->privatesAccessor = $privatesAccessor;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->astResolver = $astResolver;
    }
    /**
     * @return ClassReflection[]
     */
    public function getChildrenOfClassReflection(ClassReflection $desiredClassReflection) : array
    {
        /** @var ClassReflection[] $classReflections */
        $classReflections = $this->privatesAccessor->getPrivateProperty($this->reflectionProvider, 'classes');
        $childrenClassReflections = [];
        foreach ($classReflections as $classReflection) {
            if (!$classReflection->isSubclassOf($desiredClassReflection->getName())) {
                continue;
            }
            $childrenClassReflections[] = $classReflection;
        }
        return $childrenClassReflections;
    }
    /**
     * @param \PhpParser\Node\Name|\PhpParser\Node\ComplexType|null $propertyTypeNode
     */
    public function getPossibleUnionPropertyType(Property $property, Type $varType, ?Scope $scope, $propertyTypeNode) : PropertyType
    {
        if ($varType instanceof UnionType) {
            return new PropertyType($varType, $propertyTypeNode);
        }
        if (!$scope instanceof Scope) {
            return new PropertyType($varType, $propertyTypeNode);
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            throw new ShouldNotHappenException();
        }
        $ancestorClassReflections = \array_merge($classReflection->getParents(), $classReflection->getInterfaces());
        $propertyName = $this->nodeNameResolver->getName($property);
        $kindPropertyFetch = $this->getKindPropertyFetch($property);
        foreach ($ancestorClassReflections as $ancestorClassReflection) {
            $ancestorClassName = $ancestorClassReflection->getName();
            if ($ancestorClassReflection->isSubclassOf('PHPUnit\\Framework\\TestCase')) {
                continue;
            }
            $class = $this->astResolver->resolveClassFromClassReflection($ancestorClassReflection, $ancestorClassName);
            if (!$class instanceof Class_) {
                continue;
            }
            if (!$this->isPropertyWritten($class->stmts, $propertyName, $kindPropertyFetch)) {
                continue;
            }
            $varType = new UnionType([$varType, new NullType()]);
            $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($varType, TypeKind::PROPERTY);
            return new PropertyType($varType, $propertyTypeNode);
        }
        return new PropertyType($varType, $propertyTypeNode);
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Interface_|\PhpParser\Node\Name $classOrName
     */
    public function getClassLikeAncestorNames($classOrName) : array
    {
        $ancestorNames = [];
        if ($classOrName instanceof Name) {
            $fullName = $this->nodeNameResolver->getName($classOrName);
            $classLike = $this->astResolver->resolveClassFromName($fullName);
        } else {
            $classLike = $classOrName;
        }
        if ($classLike instanceof Interface_) {
            foreach ($classLike->extends as $extendInterfaceName) {
                $ancestorNames[] = $this->nodeNameResolver->getName($extendInterfaceName);
                $ancestorNames = \array_merge($ancestorNames, $this->getClassLikeAncestorNames($extendInterfaceName));
            }
        }
        if ($classLike instanceof Class_) {
            if ($classLike->extends instanceof Name) {
                $ancestorNames[] = $this->nodeNameResolver->getName($classLike->extends);
                $ancestorNames = \array_merge($ancestorNames, $this->getClassLikeAncestorNames($classLike->extends));
            }
            foreach ($classLike->implements as $implement) {
                $ancestorNames[] = $this->nodeNameResolver->getName($implement);
                $ancestorNames = \array_merge($ancestorNames, $this->getClassLikeAncestorNames($implement));
            }
        }
        /** @var string[] $ancestorNames */
        return $ancestorNames;
    }
    private function getKindPropertyFetch(Property $property) : string
    {
        return $property->isStatic() ? StaticPropertyFetch::class : PropertyFetch::class;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function isPropertyWritten(array $stmts, string $propertyName, string $kindPropertyFetch) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($stmts, function (Node $node) use($propertyName, $kindPropertyFetch) : bool {
            if (!$node instanceof ClassMethod) {
                return \false;
            }
            if ($this->nodeNameResolver->isName($node->name, 'autowire')) {
                return \false;
            }
            return $this->isPropertyAssignedInClassMethod($node, $propertyName, $kindPropertyFetch);
        });
    }
    private function isPropertyAssignedInClassMethod(ClassMethod $classMethod, string $propertyName, string $kindPropertyFetch) : bool
    {
        if ($classMethod->stmts === null) {
            return \false;
        }
        return (bool) $this->betterNodeFinder->findFirst($classMethod->stmts, function (Node $node) use($propertyName, $kindPropertyFetch) : bool {
            if (!$node instanceof Assign) {
                return \false;
            }
            return $kindPropertyFetch === \get_class($node->var) && $this->nodeNameResolver->isName($node->var, $propertyName);
        });
    }
}
