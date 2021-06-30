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
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PhpParser\Parser;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\FamilyTree\ValueObject\PropertyType;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
use RectorPrefix20210630\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use RectorPrefix20210630\Symplify\SmartFileSystem\SmartFileSystem;
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
     * @var \PhpParser\Parser
     */
    private $parser;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider, \RectorPrefix20210630\Symplify\PackageBuilder\Reflection\PrivatesAccessor $privatesAccessor, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \RectorPrefix20210630\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \PhpParser\Parser $parser)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->privatesAccessor = $privatesAccessor;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->smartFileSystem = $smartFileSystem;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->staticTypeMapper = $staticTypeMapper;
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
     * @param Name|NullableType|PhpParserUnionType|null $propertyTypeNode
     */
    public function getPossibleUnionPropertyType(\PhpParser\Node\Stmt\Property $property, \PHPStan\Type\Type $varType, ?\PHPStan\Analyser\Scope $scope, ?\PhpParser\Node $propertyTypeNode) : \Rector\FamilyTree\ValueObject\PropertyType
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
            $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($varType, \Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind::KIND_PROPERTY);
            return new \Rector\FamilyTree\ValueObject\PropertyType($varType, $propertyTypeNode);
        }
        return new \Rector\FamilyTree\ValueObject\PropertyType($varType, $propertyTypeNode);
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
