<?php

declare (strict_types=1);
namespace Rector\Php80\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\Generic\TemplateType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\Php80\ValueObject\PropertyPromotionCandidate;
use Rector\TypeDeclaration\TypeInferer\VarDocPropertyTypeInferer;
final class PromotedPropertyCandidateResolver
{
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
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\VarDocPropertyTypeInferer
     */
    private $varDocPropertyTypeInferer;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    public function __construct(NodeNameResolver $nodeNameResolver, BetterNodeFinder $betterNodeFinder, NodeComparator $nodeComparator, VarDocPropertyTypeInferer $varDocPropertyTypeInferer, NodeTypeResolver $nodeTypeResolver, TypeComparator $typeComparator, TypeFactory $typeFactory, PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
        $this->varDocPropertyTypeInferer = $varDocPropertyTypeInferer;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->typeComparator = $typeComparator;
        $this->typeFactory = $typeFactory;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
    }
    /**
     * @return PropertyPromotionCandidate[]
     */
    public function resolveFromClass(Class_ $class) : array
    {
        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod) {
            return [];
        }
        $propertyPromotionCandidates = [];
        foreach ($class->getProperties() as $property) {
            $propertyCount = \count($property->props);
            if ($propertyCount !== 1) {
                continue;
            }
            $propertyPromotionCandidate = $this->matchPropertyPromotionCandidate($property, $constructClassMethod);
            if (!$propertyPromotionCandidate instanceof PropertyPromotionCandidate) {
                continue;
            }
            $propertyPromotionCandidates[] = $propertyPromotionCandidate;
        }
        return $propertyPromotionCandidates;
    }
    private function matchPropertyPromotionCandidate(Property $property, ClassMethod $constructClassMethod) : ?PropertyPromotionCandidate
    {
        $onlyProperty = $property->props[0];
        $propertyName = $this->nodeNameResolver->getName($onlyProperty);
        $firstParamAsVariable = $this->resolveFirstParamUses($constructClassMethod);
        // match property name to assign in constructor
        foreach ((array) $constructClassMethod->stmts as $stmt) {
            if ($stmt instanceof Expression) {
                $stmt = $stmt->expr;
            }
            if (!$stmt instanceof Assign) {
                continue;
            }
            $assign = $stmt;
            // promoted property must use non-static property only
            if (!$assign->var instanceof PropertyFetch) {
                continue;
            }
            if (!$this->propertyFetchAnalyzer->isLocalPropertyFetchName($assign->var, $propertyName)) {
                continue;
            }
            // 1. is param
            $assignedExpr = $assign->expr;
            if (!$assignedExpr instanceof Variable) {
                continue;
            }
            $matchedParam = $this->matchClassMethodParamByAssignedVariable($constructClassMethod, $assignedExpr);
            if (!$matchedParam instanceof Param) {
                continue;
            }
            if ($this->shouldSkipParam($matchedParam, $property, $assignedExpr, $firstParamAsVariable)) {
                continue;
            }
            return new PropertyPromotionCandidate($property, $assign, $matchedParam);
        }
        return null;
    }
    /**
     * @return array<string, int>
     */
    private function resolveFirstParamUses(ClassMethod $classMethod) : array
    {
        $paramByFirstUsage = [];
        foreach ($classMethod->params as $param) {
            $paramName = $this->nodeNameResolver->getName($param);
            $firstParamVariable = $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (Node $node) use($paramName) : bool {
                if (!$node instanceof Variable) {
                    return \false;
                }
                return $this->nodeNameResolver->isName($node, $paramName);
            });
            if (!$firstParamVariable instanceof Node) {
                continue;
            }
            $paramByFirstUsage[$paramName] = $firstParamVariable->getStartTokenPos();
        }
        return $paramByFirstUsage;
    }
    private function matchClassMethodParamByAssignedVariable(ClassMethod $classMethod, Variable $variable) : ?Param
    {
        foreach ($classMethod->params as $param) {
            if (!$this->nodeComparator->areNodesEqual($variable, $param->var)) {
                continue;
            }
            return $param;
        }
        return null;
    }
    /**
     * @param array<string, int> $firstParamAsVariable
     */
    private function isParamUsedBeforeAssign(Variable $variable, array $firstParamAsVariable) : bool
    {
        $variableName = $this->nodeNameResolver->getName($variable);
        $firstVariablePosition = $firstParamAsVariable[$variableName] ?? null;
        if ($firstVariablePosition === null) {
            return \false;
        }
        return $firstVariablePosition < $variable->getStartTokenPos();
    }
    private function hasConflictingParamType(Param $param, Type $propertyType) : bool
    {
        if ($param->type === null) {
            return \false;
        }
        $matchedParamType = $this->nodeTypeResolver->getType($param);
        if ($param->default !== null) {
            $defaultValueType = $this->nodeTypeResolver->getType($param->default);
            $matchedParamType = $this->typeFactory->createMixedPassedOrUnionType([$matchedParamType, $defaultValueType]);
        }
        if (!$propertyType instanceof UnionType) {
            return \false;
        }
        if ($this->typeComparator->areTypesEqual($propertyType, $matchedParamType)) {
            return \false;
        }
        // different types, check not has mixed and not has templated generic types
        if (!$this->hasMixedType($propertyType)) {
            return \false;
        }
        return !$this->hasTemplatedGenericType($propertyType);
    }
    private function hasTemplatedGenericType(UnionType $unionType) : bool
    {
        foreach ($unionType->getTypes() as $type) {
            if ($type instanceof TemplateType) {
                return \true;
            }
        }
        return \false;
    }
    private function hasMixedType(UnionType $unionType) : bool
    {
        foreach ($unionType->getTypes() as $type) {
            if ($type instanceof MixedType) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param int[] $firstParamAsVariable
     */
    private function shouldSkipParam(Param $matchedParam, Property $property, Variable $assignedVariable, array $firstParamAsVariable) : bool
    {
        // already promoted
        if ($matchedParam->flags !== 0) {
            return \true;
        }
        if ($this->isParamUsedBeforeAssign($assignedVariable, $firstParamAsVariable)) {
            return \true;
        }
        // @todo unknown type, not suitable?
        $propertyType = $this->varDocPropertyTypeInferer->inferProperty($property);
        return $this->hasConflictingParamType($matchedParam, $propertyType);
    }
}
