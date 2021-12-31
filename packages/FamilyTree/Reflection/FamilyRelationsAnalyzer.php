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
use Rector\FamilyTree\ValueObject\PropertyType;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
use RectorPrefix20211231\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
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
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider, \RectorPrefix20211231\Symplify\PackageBuilder\Reflection\PrivatesAccessor $privatesAccessor, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \Rector\Core\PhpParser\AstResolver $astResolver)
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
    public function getChildrenOfClassReflection(\PHPStan\Reflection\ClassReflection $desiredClassReflection) : array
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
     * @param \PhpParser\Node\ComplexType|\PhpParser\Node\Name|null $propertyTypeNode
     */
    public function getPossibleUnionPropertyType(\PhpParser\Node\Stmt\Property $property, \PHPStan\Type\Type $varType, ?\PHPStan\Analyser\Scope $scope, $propertyTypeNode) : \Rector\FamilyTree\ValueObject\PropertyType
    {
        if ($varType instanceof \PHPStan\Type\UnionType) {
            return new \Rector\FamilyTree\ValueObject\PropertyType($varType, $propertyTypeNode);
        }
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return new \Rector\FamilyTree\ValueObject\PropertyType($varType, $propertyTypeNode);
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
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
            if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
                continue;
            }
            if (!$this->isPropertyWritten($class->stmts, $propertyName, $kindPropertyFetch)) {
                continue;
            }
            $varType = new \PHPStan\Type\UnionType([$varType, new \PHPStan\Type\NullType()]);
            $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($varType, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::PROPERTY());
            return new \Rector\FamilyTree\ValueObject\PropertyType($varType, $propertyTypeNode);
        }
        return new \Rector\FamilyTree\ValueObject\PropertyType($varType, $propertyTypeNode);
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Name|\PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Interface_ $classOrName
     */
    public function getClassLikeAncestorNames($classOrName) : array
    {
        $ancestorNames = [];
        if ($classOrName instanceof \PhpParser\Node\Name) {
            $fullName = $this->nodeNameResolver->getName($classOrName);
            $classLike = $this->astResolver->resolveClassFromName($fullName);
        } else {
            $classLike = $classOrName;
        }
        if ($classLike instanceof \PhpParser\Node\Stmt\Interface_) {
            foreach ($classLike->extends as $extendInterfaceName) {
                $ancestorNames[] = $this->nodeNameResolver->getName($extendInterfaceName);
                $ancestorNames = \array_merge($ancestorNames, $this->getClassLikeAncestorNames($extendInterfaceName));
            }
        }
        if ($classLike instanceof \PhpParser\Node\Stmt\Class_) {
            if ($classLike->extends instanceof \PhpParser\Node\Name) {
                $extendName = $classLike->extends;
                $ancestorNames[] = $this->nodeNameResolver->getName($extendName);
                $ancestorNames = \array_merge($ancestorNames, $this->getClassLikeAncestorNames($extendName));
            }
            foreach ($classLike->implements as $implement) {
                $ancestorNames[] = $this->nodeNameResolver->getName($implement);
                $ancestorNames = \array_merge($ancestorNames, $this->getClassLikeAncestorNames($implement));
            }
        }
        /** @var string[] $ancestorNames */
        return $ancestorNames;
    }
    private function getKindPropertyFetch(\PhpParser\Node\Stmt\Property $property) : string
    {
        return $property->isStatic() ? \PhpParser\Node\Expr\StaticPropertyFetch::class : \PhpParser\Node\Expr\PropertyFetch::class;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function isPropertyWritten(array $stmts, string $propertyName, string $kindPropertyFetch) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($stmts, function (\PhpParser\Node $node) use($propertyName, $kindPropertyFetch) : bool {
            if (!$node instanceof \PhpParser\Node\Stmt\ClassMethod) {
                return \false;
            }
            if ($this->nodeNameResolver->isName($node->name, 'autowire')) {
                return \false;
            }
            return $this->isPropertyAssignedInClassMethod($node, $propertyName, $kindPropertyFetch);
        });
    }
    private function isPropertyAssignedInClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod, string $propertyName, string $kindPropertyFetch) : bool
    {
        if ($classMethod->stmts === null) {
            return \false;
        }
        return (bool) $this->betterNodeFinder->findFirst($classMethod->stmts, function (\PhpParser\Node $node) use($propertyName, $kindPropertyFetch) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\Assign) {
                return \false;
            }
            return $kindPropertyFetch === \get_class($node->var) && $this->nodeNameResolver->isName($node->var, $propertyName);
        });
    }
}
