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
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeVisitor;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use Rector\NodeAnalyzer\ParamAnalyzer;
use Rector\NodeManipulator\ClassMethodPropertyFetchManipulator;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer;
/**
 * @internal
 */
final class TrustedClassMethodPropertyTypeInferer
{
    /**
     * @readonly
     */
    private ClassMethodPropertyFetchManipulator $classMethodPropertyFetchManipulator;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private SimpleCallableNodeTraverser $simpleCallableNodeTraverser;
    /**
     * @readonly
     */
    private TypeFactory $typeFactory;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    /**
     * @readonly
     */
    private ParamAnalyzer $paramAnalyzer;
    /**
     * @readonly
     */
    private AssignToPropertyTypeInferer $assignToPropertyTypeInferer;
    /**
     * @readonly
     */
    private TypeComparator $typeComparator;
    public function __construct(ClassMethodPropertyFetchManipulator $classMethodPropertyFetchManipulator, ReflectionProvider $reflectionProvider, NodeNameResolver $nodeNameResolver, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, TypeFactory $typeFactory, StaticTypeMapper $staticTypeMapper, NodeTypeResolver $nodeTypeResolver, ParamAnalyzer $paramAnalyzer, AssignToPropertyTypeInferer $assignToPropertyTypeInferer, TypeComparator $typeComparator)
    {
        $this->classMethodPropertyFetchManipulator = $classMethodPropertyFetchManipulator;
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->typeFactory = $typeFactory;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->paramAnalyzer = $paramAnalyzer;
        $this->assignToPropertyTypeInferer = $assignToPropertyTypeInferer;
        $this->typeComparator = $typeComparator;
    }
    public function inferProperty(Class_ $class, Property $property, ClassMethod $classMethod) : Type
    {
        $propertyName = $this->nodeNameResolver->getName($property);
        // 1. direct property = param assign
        $param = $this->classMethodPropertyFetchManipulator->findParamAssignToPropertyName($classMethod, $propertyName);
        if ($param instanceof Param) {
            return $this->resolveTypeFromParam($param, $classMethod, $propertyName, $property, $class);
        }
        // 2. different assign
        /** @var Expr[] $assignedExprs */
        $assignedExprs = $this->classMethodPropertyFetchManipulator->findAssignsToPropertyName($classMethod, $propertyName);
        $resolvedTypes = [];
        foreach ($assignedExprs as $assignedExpr) {
            $resolvedTypes[] = $this->nodeTypeResolver->getNativeType($assignedExpr);
        }
        if ($resolvedTypes === []) {
            return new MixedType();
        }
        $resolvedType = \count($resolvedTypes) === 1 ? $resolvedTypes[0] : TypeCombinator::union(...$resolvedTypes);
        return $this->resolveType($property, $propertyName, $class, $resolvedType);
    }
    private function resolveType(Property $property, string $propertyName, Class_ $class, Type $resolvedType) : Type
    {
        $exactType = $this->assignToPropertyTypeInferer->inferPropertyInClassLike($property, $propertyName, $class);
        if (!$exactType instanceof UnionType) {
            return $resolvedType;
        }
        if ($this->typeComparator->areTypesEqual($resolvedType, $exactType)) {
            return $resolvedType;
        }
        return new MixedType();
    }
    private function resolveFromParamType(Param $param, ClassMethod $classMethod, string $propertyName) : Type
    {
        $type = $this->resolveParamTypeToPHPStanType($param);
        if ($type instanceof MixedType) {
            return new MixedType();
        }
        $types = [];
        // it's an array - annotation → make type more precise, if possible
        if ($type->isArray()->yes() || $param->variadic) {
            $types[] = $this->getResolveParamStaticTypeAsPHPStanType($classMethod, $propertyName);
        } else {
            $types[] = $type;
        }
        if ($this->isParamNullable($param)) {
            $types[] = new NullType();
        }
        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }
    private function resolveParamTypeToPHPStanType(Param $param) : Type
    {
        if (!$param->type instanceof Node) {
            return new MixedType();
        }
        if ($this->paramAnalyzer->isNullable($param)) {
            /** @var NullableType $type */
            $type = $param->type;
            $types = [];
            $types[] = new NullType();
            $types[] = $this->staticTypeMapper->mapPhpParserNodePHPStanType($type->type);
            return $this->typeFactory->createMixedPassedOrUnionType($types);
        }
        // special case for alias
        if ($param->type instanceof FullyQualified) {
            $type = $this->resolveFullyQualifiedOrAliasedObjectType($param);
            if ($type instanceof Type) {
                return $type;
            }
        }
        return $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
    }
    private function getResolveParamStaticTypeAsPHPStanType(ClassMethod $classMethod, string $propertyName) : Type
    {
        $paramStaticType = new ArrayType(new MixedType(), new MixedType());
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use($propertyName, &$paramStaticType) : ?int {
            if (!$node instanceof Variable) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node, $propertyName)) {
                return null;
            }
            $paramStaticType = $this->nodeTypeResolver->getType($node);
            return NodeVisitor::STOP_TRAVERSAL;
        });
        return $paramStaticType;
    }
    private function isParamNullable(Param $param) : bool
    {
        if ($this->paramAnalyzer->isNullable($param)) {
            return \true;
        }
        if ($param->default instanceof Expr) {
            $defaultValueStaticType = $this->nodeTypeResolver->getType($param->default);
            if ($defaultValueStaticType->isNull()->yes()) {
                return \true;
            }
        }
        return \false;
    }
    private function resolveFullyQualifiedOrAliasedObjectType(Param $param) : ?Type
    {
        if (!$param->type instanceof Node) {
            return null;
        }
        $fullyQualifiedName = $this->nodeNameResolver->getName($param->type);
        if (!\is_string($fullyQualifiedName)) {
            return null;
        }
        $originalName = $param->type->getAttribute(AttributeKey::ORIGINAL_NAME);
        if (!$originalName instanceof Name) {
            return null;
        }
        // if the FQN has different ending than the original, it was aliased and we need to return the alias
        if (\substr_compare($fullyQualifiedName, '\\' . $originalName->toString(), -\strlen('\\' . $originalName->toString())) !== 0) {
            $className = $originalName->toString();
            if ($this->reflectionProvider->hasClass($className)) {
                return new FullyQualifiedObjectType($className);
            }
            // @note: $fullyQualifiedName is a guess, needs real life test
            return new AliasedObjectType($originalName->toString(), $fullyQualifiedName);
        }
        return null;
    }
    private function resolveTypeFromParam(Param $param, ClassMethod $classMethod, string $propertyName, Property $property, Class_ $class) : Type
    {
        if (!$param->type instanceof Node) {
            return new MixedType();
        }
        $resolvedType = $this->resolveFromParamType($param, $classMethod, $propertyName);
        return $this->resolveType($property, $propertyName, $class, $resolvedType);
    }
}
