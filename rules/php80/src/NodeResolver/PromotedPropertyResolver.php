<?php

declare(strict_types=1);

namespace Rector\Php80\NodeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
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
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        BetterStandardPrinter $betterStandardPrinter,
        BetterNodeFinder $betterNodeFinder
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->betterNodeFinder = $betterNodeFinder;
    }

    /**
     * @return PropertyPromotionCandidate[]
     */
    public function resolveFromClass(Class_ $class): array
    {
        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (! $constructClassMethod instanceof ClassMethod) {
            return [];
        }

        $propertyPromotionCandidates = [];
        foreach ($class->getProperties() as $property) {
            if (count($property->props) !== 1) {
                continue;
            }

            $propertyPromotionCandidate = $this->matchPropertyPromotionCandidate($property, $constructClassMethod);
            if (! $propertyPromotionCandidate instanceof PropertyPromotionCandidate) {
                continue;
            }

            $propertyPromotionCandidates[] = $propertyPromotionCandidate;
        }

        return $propertyPromotionCandidates;
    }

    private function matchPropertyPromotionCandidate(
        Property $property,
        ClassMethod $constructClassMethod
    ): ?PropertyPromotionCandidate {
        $onlyProperty = $property->props[0];

        $propertyName = $this->nodeNameResolver->getName($onlyProperty);

        $firstParamAsVariable = $this->resolveFirstParamUses($constructClassMethod);

        // match property name to assign in constructor
        foreach ((array) $constructClassMethod->stmts as $stmt) {
            if ($stmt instanceof Expression) {
                $stmt = $stmt->expr;
            }

            if (! $stmt instanceof Assign) {
                continue;
            }

            $assign = $stmt;
            if (! $this->nodeNameResolver->isLocalPropertyFetchNamed($assign->var, $propertyName)) {
                continue;
            }

            // 1. is param
            // @todo 2. is default value
            $assignedExpr = $assign->expr;

            if (! $assignedExpr instanceof Variable) {
                continue;
            }

            $matchedParam = $this->matchClassMethodParamByAssignedVariable($constructClassMethod, $assignedExpr);
            if (! $matchedParam instanceof Param) {
                continue;
            }

            // is param used above assign?
            if ($this->isParamUsedBeforeAssign($assignedExpr, $firstParamAsVariable)) {
                continue;
            }

            return new PropertyPromotionCandidate($property, $assign, $matchedParam);
        }

        return null;
    }

    /**
     * @return array<string, int>
     */
    private function resolveFirstParamUses(ClassMethod $classMethod): array
    {
        $paramByFirstUsage = [];
        foreach ($classMethod->params as $param) {
            $paramName = $this->nodeNameResolver->getName($param);

            $firstParamVariable = $this->betterNodeFinder->findFirst($classMethod->stmts, function (Node $node) use (
                $paramName
            ): bool {
                if (! $node instanceof Variable) {
                    return false;
                }

                return $this->nodeNameResolver->isName($node, $paramName);
            });

            if (! $firstParamVariable instanceof Node) {
                continue;
            }

            $paramByFirstUsage[$paramName] = $firstParamVariable->getStartTokenPos();
        }

        return $paramByFirstUsage;
    }

    private function matchClassMethodParamByAssignedVariable(
        ClassMethod $classMethod,
        Variable $variable
    ): ?Param {
        foreach ($classMethod->params as $param) {
            if (! $this->betterStandardPrinter->areNodesEqual($variable, $param->var)) {
                continue;
            }

            return $param;
        }

        return null;
    }

    /**
     * @param array<string, int> $firstParamAsVariable
     */
    private function isParamUsedBeforeAssign(Variable $variable, array $firstParamAsVariable): bool
    {
        $variableName = $this->nodeNameResolver->getName($variable);

        $firstVariablePosition = $firstParamAsVariable[$variableName] ?? null;
        if ($firstVariablePosition === null) {
            return false;
        }

        return $firstVariablePosition < $variable->getStartTokenPos();
    }
}
