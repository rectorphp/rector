<?php

declare (strict_types=1);
namespace Rector\Php80\NodeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Php80\ValueObject\PropertyPromotionCandidate;
final class PromotedPropertyResolver
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var NodeComparator
     */
    private $nodeComparator;
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
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
            if (\count($property->props) !== 1) {
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
            // @todo 2. is default value
            $assignedExpr = $assign->expr;
            if (!$assignedExpr instanceof \PhpParser\Node\Expr\Variable) {
                continue;
            }
            $matchedParam = $this->matchClassMethodParamByAssignedVariable($constructClassMethod, $assignedExpr);
            if (!$matchedParam instanceof \PhpParser\Node\Param) {
                continue;
            }
            // is param used above assign?
            if ($this->isParamUsedBeforeAssign($assignedExpr, $firstParamAsVariable)) {
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
}
