<?php

declare (strict_types=1);
namespace Rector\Php80\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
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
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\Php80\ValueObject\PropertyPromotionCandidate;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;
final class PromotedPropertyCandidateResolver
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @var \Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer
     */
    private $propertyTypeInferer;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    /**
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer $propertyTypeInferer, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\NodeTypeResolver\TypeComparator\TypeComparator $typeComparator, \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory $typeFactory)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
        $this->propertyTypeInferer = $propertyTypeInferer;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->typeComparator = $typeComparator;
        $this->typeFactory = $typeFactory;
    }
    /**
     * @return PropertyPromotionCandidate[]
     */
    public function resolveFromClass(\PhpParser\Node\Stmt\Class_ $class) : array
    {
        $constructClassMethod = $class->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return [];
        }
        $propertyPromotionCandidates = [];
        foreach ($class->getProperties() as $property) {
            $propertyCount = \count($property->props);
            if ($propertyCount !== 1) {
                continue;
            }
            $propertyPromotionCandidate = $this->matchPropertyPromotionCandidate($property, $constructClassMethod);
            if (!$propertyPromotionCandidate instanceof \Rector\Php80\ValueObject\PropertyPromotionCandidate) {
                continue;
            }
            $propertyPromotionCandidates[] = $propertyPromotionCandidate;
        }
        return $propertyPromotionCandidates;
    }
    private function matchPropertyPromotionCandidate(\PhpParser\Node\Stmt\Property $property, \PhpParser\Node\Stmt\ClassMethod $constructClassMethod) : ?\Rector\Php80\ValueObject\PropertyPromotionCandidate
    {
        $onlyProperty = $property->props[0];
        $propertyName = $this->nodeNameResolver->getName($onlyProperty);
        $firstParamAsVariable = $this->resolveFirstParamUses($constructClassMethod);
        // match property name to assign in constructor
        foreach ((array) $constructClassMethod->stmts as $stmt) {
            if ($stmt instanceof \PhpParser\Node\Stmt\Expression) {
                $stmt = $stmt->expr;
            }
            if (!$stmt instanceof \PhpParser\Node\Expr\Assign) {
                continue;
            }
            $assign = $stmt;
            if (!$this->nodeNameResolver->isLocalPropertyFetchNamed($assign->var, $propertyName)) {
                continue;
            }
            // 1. is param
            $assignedExpr = $assign->expr;
            if (!$assignedExpr instanceof \PhpParser\Node\Expr\Variable) {
                continue;
            }
            $matchedParam = $this->matchClassMethodParamByAssignedVariable($constructClassMethod, $assignedExpr);
            if (!$matchedParam instanceof \PhpParser\Node\Param) {
                continue;
            }
            if ($this->shouldSkipParam($matchedParam, $property, $assignedExpr, $firstParamAsVariable)) {
                continue;
            }
            return new \Rector\Php80\ValueObject\PropertyPromotionCandidate($property, $assign, $matchedParam);
        }
        return null;
    }
    /**
     * @return array<string, int>
     */
    private function resolveFirstParamUses(\PhpParser\Node\Stmt\ClassMethod $classMethod) : array
    {
        $paramByFirstUsage = [];
        foreach ($classMethod->params as $param) {
            $paramName = $this->nodeNameResolver->getName($param);
            $firstParamVariable = $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (\PhpParser\Node $node) use($paramName) : bool {
                if (!$node instanceof \PhpParser\Node\Expr\Variable) {
                    return \false;
                }
                return $this->nodeNameResolver->isName($node, $paramName);
            });
            if (!$firstParamVariable instanceof \PhpParser\Node) {
                continue;
            }
            $paramByFirstUsage[$paramName] = $firstParamVariable->getStartTokenPos();
        }
        return $paramByFirstUsage;
    }
    private function matchClassMethodParamByAssignedVariable(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PhpParser\Node\Expr\Variable $variable) : ?\PhpParser\Node\Param
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
    private function isParamUsedBeforeAssign(\PhpParser\Node\Expr\Variable $variable, array $firstParamAsVariable) : bool
    {
        $variableName = $this->nodeNameResolver->getName($variable);
        $firstVariablePosition = $firstParamAsVariable[$variableName] ?? null;
        if ($firstVariablePosition === null) {
            return \false;
        }
        return $firstVariablePosition < $variable->getStartTokenPos();
    }
    private function hasConflictingParamType(\PhpParser\Node\Param $param, \PHPStan\Type\Type $propertyType) : bool
    {
        if ($param->type === null) {
            return \false;
        }
        $matchedParamType = $this->nodeTypeResolver->getType($param);
        if ($param->default !== null) {
            $defaultValueType = $this->nodeTypeResolver->getType($param->default);
            $matchedParamType = $this->typeFactory->createMixedPassedOrUnionType([$matchedParamType, $defaultValueType]);
        }
        if (!$propertyType instanceof \PHPStan\Type\UnionType) {
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
    private function hasTemplatedGenericType(\PHPStan\Type\UnionType $unionType) : bool
    {
        foreach ($unionType->getTypes() as $type) {
            if ($type instanceof \PHPStan\Type\Generic\TemplateType) {
                return \true;
            }
        }
        return \false;
    }
    private function hasMixedType(\PHPStan\Type\UnionType $unionType) : bool
    {
        foreach ($unionType->getTypes() as $type) {
            if ($type instanceof \PHPStan\Type\MixedType) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param int[] $firstParamAsVariable
     */
    private function shouldSkipParam(\PhpParser\Node\Param $matchedParam, \PhpParser\Node\Stmt\Property $property, \PhpParser\Node\Expr\Variable $assignedVariable, array $firstParamAsVariable) : bool
    {
        // already promoted
        if ($matchedParam->flags !== 0) {
            return \true;
        }
        if ($this->isParamUsedBeforeAssign($assignedVariable, $firstParamAsVariable)) {
            return \true;
        }
        // @todo unknown type, not suitable?
        $propertyType = $this->propertyTypeInferer->inferProperty($property);
        return $this->hasConflictingParamType($matchedParam, $propertyType);
    }
}
