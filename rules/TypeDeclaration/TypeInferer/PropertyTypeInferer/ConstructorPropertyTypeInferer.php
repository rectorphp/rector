<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeTraverser;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use Rector\Core\NodeAnalyzer\ParamAnalyzer;
use Rector\Core\NodeManipulator\ClassMethodPropertyFetchManipulator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer;
use RectorPrefix20220501\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class ConstructorPropertyTypeInferer
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassMethodPropertyFetchManipulator
     */
    private $classMethodPropertyFetchManipulator;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ParamAnalyzer
     */
    private $paramAnalyzer;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer
     */
    private $assignToPropertyTypeInferer;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    public function __construct(\Rector\Core\NodeManipulator\ClassMethodPropertyFetchManipulator $classMethodPropertyFetchManipulator, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \RectorPrefix20220501\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory $typeFactory, \Rector\StaticTypeMapper\StaticTypeMapper $staticTypeMapper, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\NodeAnalyzer\ParamAnalyzer $paramAnalyzer, \Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer $assignToPropertyTypeInferer, \Rector\NodeTypeResolver\TypeComparator\TypeComparator $typeComparator)
    {
        $this->classMethodPropertyFetchManipulator = $classMethodPropertyFetchManipulator;
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->typeFactory = $typeFactory;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->paramAnalyzer = $paramAnalyzer;
        $this->assignToPropertyTypeInferer = $assignToPropertyTypeInferer;
        $this->typeComparator = $typeComparator;
    }
    public function inferProperty(\PhpParser\Node\Stmt\Property $property) : ?\PHPStan\Type\Type
    {
        $classLike = $this->betterNodeFinder->findParentType($property, \PhpParser\Node\Stmt\ClassLike::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return null;
        }
        $classMethod = $classLike->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return null;
        }
        $propertyName = $this->nodeNameResolver->getName($property);
        // 1. direct property = param assign
        $param = $this->classMethodPropertyFetchManipulator->findParamAssignToPropertyName($classMethod, $propertyName);
        if ($param instanceof \PhpParser\Node\Param) {
            if ($param->type === null) {
                return null;
            }
            $resolvedType = $this->resolveFromParamType($param, $classMethod, $propertyName);
            return $this->resolveType($property, $propertyName, $classLike, $resolvedType);
        }
        // 2. different assign
        /** @var Expr[] $assignedExprs */
        $assignedExprs = $this->classMethodPropertyFetchManipulator->findAssignsToPropertyName($classMethod, $propertyName);
        $resolvedTypes = [];
        foreach ($assignedExprs as $assignedExpr) {
            $resolvedTypes[] = $this->nodeTypeResolver->getType($assignedExpr);
        }
        if ($resolvedTypes === []) {
            return null;
        }
        $resolvedType = \count($resolvedTypes) === 1 ? $resolvedTypes[0] : \PHPStan\Type\TypeCombinator::union(...$resolvedTypes);
        return $this->resolveType($property, $propertyName, $classLike, $resolvedType);
    }
    private function resolveType(\PhpParser\Node\Stmt\Property $property, string $propertyName, \PhpParser\Node\Stmt\ClassLike $classLike, ?\PHPStan\Type\Type $resolvedType) : ?\PHPStan\Type\Type
    {
        if (!$resolvedType instanceof \PHPStan\Type\Type) {
            return null;
        }
        $exactType = $this->assignToPropertyTypeInferer->inferPropertyInClassLike($property, $propertyName, $classLike);
        if (!$exactType instanceof \PHPStan\Type\UnionType) {
            return $resolvedType;
        }
        if ($this->typeComparator->areTypesEqual($resolvedType, $exactType)) {
            return $resolvedType;
        }
        return null;
    }
    private function resolveFromParamType(\PhpParser\Node\Param $param, \PhpParser\Node\Stmt\ClassMethod $classMethod, string $propertyName) : \PHPStan\Type\Type
    {
        $type = $this->resolveParamTypeToPHPStanType($param);
        if ($type instanceof \PHPStan\Type\MixedType) {
            return new \PHPStan\Type\MixedType();
        }
        $types = [];
        // it's an array - annotation → make type more precise, if possible
        if ($type instanceof \PHPStan\Type\ArrayType || $param->variadic) {
            $types[] = $this->getResolveParamStaticTypeAsPHPStanType($classMethod, $propertyName);
        } else {
            $types[] = $type;
        }
        if ($this->isParamNullable($param)) {
            $types[] = new \PHPStan\Type\NullType();
        }
        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }
    private function resolveParamTypeToPHPStanType(\PhpParser\Node\Param $param) : \PHPStan\Type\Type
    {
        if ($param->type === null) {
            return new \PHPStan\Type\MixedType();
        }
        if ($this->paramAnalyzer->isNullable($param)) {
            /** @var NullableType $type */
            $type = $param->type;
            $types = [];
            $types[] = new \PHPStan\Type\NullType();
            $types[] = $this->staticTypeMapper->mapPhpParserNodePHPStanType($type->type);
            return $this->typeFactory->createMixedPassedOrUnionType($types);
        }
        // special case for alias
        if ($param->type instanceof \PhpParser\Node\Name\FullyQualified) {
            $type = $this->resolveFullyQualifiedOrAliasedObjectType($param);
            if ($type !== null) {
                return $type;
            }
        }
        return $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
    }
    private function getResolveParamStaticTypeAsPHPStanType(\PhpParser\Node\Stmt\ClassMethod $classMethod, string $propertyName) : \PHPStan\Type\Type
    {
        $paramStaticType = new \PHPStan\Type\ArrayType(new \PHPStan\Type\MixedType(), new \PHPStan\Type\MixedType());
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (\PhpParser\Node $node) use($propertyName, &$paramStaticType) : ?int {
            if (!$node instanceof \PhpParser\Node\Expr\Variable) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node, $propertyName)) {
                return null;
            }
            $paramStaticType = $this->nodeTypeResolver->getType($node);
            return \PhpParser\NodeTraverser::STOP_TRAVERSAL;
        });
        return $paramStaticType;
    }
    private function isParamNullable(\PhpParser\Node\Param $param) : bool
    {
        if ($this->paramAnalyzer->isNullable($param)) {
            return \true;
        }
        if ($param->default !== null) {
            $defaultValueStaticType = $this->nodeTypeResolver->getType($param->default);
            if ($defaultValueStaticType instanceof \PHPStan\Type\NullType) {
                return \true;
            }
        }
        return \false;
    }
    private function resolveFullyQualifiedOrAliasedObjectType(\PhpParser\Node\Param $param) : ?\PHPStan\Type\Type
    {
        if ($param->type === null) {
            return null;
        }
        $fullyQualifiedName = $this->nodeNameResolver->getName($param->type);
        if (!\is_string($fullyQualifiedName)) {
            return null;
        }
        $originalName = $param->type->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NAME);
        if (!$originalName instanceof \PhpParser\Node\Name) {
            return null;
        }
        // if the FQN has different ending than the original, it was aliased and we need to return the alias
        if (\substr_compare($fullyQualifiedName, '\\' . $originalName->toString(), -\strlen('\\' . $originalName->toString())) !== 0) {
            $className = $originalName->toString();
            if ($this->reflectionProvider->hasClass($className)) {
                return new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($className);
            }
            // @note: $fullyQualifiedName is a guess, needs real life test
            return new \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType($originalName->toString(), $fullyQualifiedName);
        }
        return null;
    }
}
