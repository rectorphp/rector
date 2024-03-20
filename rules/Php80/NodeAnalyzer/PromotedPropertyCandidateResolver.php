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
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Php80\ValueObject\PropertyPromotionCandidate;
use Rector\PhpParser\Comparing\NodeComparator;
use Rector\PhpParser\Node\BetterNodeFinder;
final class PromotedPropertyCandidateResolver
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    public function __construct(NodeNameResolver $nodeNameResolver, BetterNodeFinder $betterNodeFinder, NodeComparator $nodeComparator, PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
    }
    /**
     * @return PropertyPromotionCandidate[]
     */
    public function resolveFromClass(Class_ $class, ClassMethod $constructClassMethod) : array
    {
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
        if ($property->flags === 0) {
            return null;
        }
        $onlyProperty = $property->props[0];
        $propertyName = $this->nodeNameResolver->getName($onlyProperty);
        $firstParamAsVariable = $this->resolveFirstParamUses($constructClassMethod);
        // match property name to assign in constructor
        foreach ((array) $constructClassMethod->stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof Assign) {
                continue;
            }
            $assign = $stmt->expr;
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
            if ($this->shouldSkipParam($matchedParam, $assignedExpr, $firstParamAsVariable)) {
                continue;
            }
            return new PropertyPromotionCandidate($property, $matchedParam, $stmt);
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
    /**
     * @param int[] $firstParamAsVariable
     */
    private function shouldSkipParam(Param $matchedParam, Variable $assignedVariable, array $firstParamAsVariable) : bool
    {
        // already promoted
        if ($matchedParam->flags !== 0) {
            return \true;
        }
        return $this->isParamUsedBeforeAssign($assignedVariable, $firstParamAsVariable);
    }
}
