<?php

declare (strict_types=1);
namespace Rector\FamilyTree\Reflection;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\Util\Reflection\PrivatesAccessor;
use Rector\FamilyTree\ValueObject\PropertyType;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class FamilyRelationsAnalyzer
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Core\Util\Reflection\PrivatesAccessor
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
    public function getPossibleUnionPropertyType(Property $property, Type $varType, Scope $scope, $propertyTypeNode) : PropertyType
    {
        if ($varType instanceof UnionType) {
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
            if ($ancestorClassReflection->isSubclassOf('PHPUnit\\Framework\\TestCase')) {
                continue;
            }
            $class = $this->astResolver->resolveClassFromClassReflection($ancestorClassReflection);
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
     * @api
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
