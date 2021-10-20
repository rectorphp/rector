<?php

declare (strict_types=1);
namespace Rector\FamilyTree\Reflection;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PhpParser\Parser;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\FamilyTree\ValueObject\PropertyType;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
use RectorPrefix20211020\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use RectorPrefix20211020\Symplify\SmartFileSystem\SmartFileSystem;
final class FamilyRelationsAnalyzer
{
    /**
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var \Symplify\PackageBuilder\Reflection\PrivatesAccessor
     */
    private $privatesAccessor;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @var \PhpParser\Parser
     */
    private $parser;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider, \RectorPrefix20211020\Symplify\PackageBuilder\Reflection\PrivatesAccessor $privatesAccessor, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \RectorPrefix20211020\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \Rector\Core\PhpParser\AstResolver $astResolver, \PhpParser\Parser $parser)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->privatesAccessor = $privatesAccessor;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->smartFileSystem = $smartFileSystem;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->astResolver = $astResolver;
        $this->parser = $parser;
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
     * @param \PhpParser\Node\Name|\PhpParser\Node\NullableType|PhpParserUnionType|null $propertyTypeNode
     */
    public function getPossibleUnionPropertyType(\PhpParser\Node\Stmt\Property $property, \PHPStan\Type\Type $varType, ?\PHPStan\Analyser\Scope $scope, $propertyTypeNode) : \Rector\FamilyTree\ValueObject\PropertyType
    {
        if ($varType instanceof \PHPStan\Type\UnionType) {
            return new \Rector\FamilyTree\ValueObject\PropertyType($varType, $propertyTypeNode);
        }
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return new \Rector\FamilyTree\ValueObject\PropertyType($varType, $propertyTypeNode);
        }
        /** @var ClassReflection $classReflection */
        $classReflection = $scope->getClassReflection();
        $ancestors = $classReflection->getAncestors();
        $propertyName = $this->nodeNameResolver->getName($property);
        $kindPropertyFetch = $this->getKindPropertyFetch($property);
        $className = $property->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        foreach ($ancestors as $ancestor) {
            $ancestorName = $ancestor->getName();
            if ($ancestorName === $className) {
                continue;
            }
            $fileName = $ancestor->getFileName();
            if ($fileName === \false) {
                continue;
            }
            $fileContent = $this->smartFileSystem->readFile($fileName);
            $nodes = $this->parser->parse($fileContent);
            if ($ancestor->isSubclassOf('PHPUnit\\Framework\\TestCase')) {
                continue;
            }
            if ($nodes === null) {
                continue;
            }
            if (!$this->isPropertyWritten($nodes, $propertyName, $kindPropertyFetch)) {
                continue;
            }
            $varType = new \PHPStan\Type\UnionType([$varType, new \PHPStan\Type\NullType()]);
            $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($varType, \Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind::PROPERTY());
            return new \Rector\FamilyTree\ValueObject\PropertyType($varType, $propertyTypeNode);
        }
        return new \Rector\FamilyTree\ValueObject\PropertyType($varType, $propertyTypeNode);
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Interface_|\PhpParser\Node\Name $classOrName
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
     * @param Stmt[] $nodes
     */
    private function isPropertyWritten(array $nodes, string $propertyName, string $kindPropertyFetch) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($nodes, function (\PhpParser\Node $node) use($propertyName, $kindPropertyFetch) : bool {
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
