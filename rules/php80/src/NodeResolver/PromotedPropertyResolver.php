<?php

declare(strict_types=1);

namespace Rector\Php80\NodeResolver;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
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

    public function __construct(NodeNameResolver $nodeNameResolver, BetterStandardPrinter $betterStandardPrinter)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    /**
     * @return PropertyPromotionCandidate[]
     */
    public function resolveFromClass(Class_ $class): array
    {
        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if ($constructClassMethod === null) {
            return [];
        }

        $propertyPromotionCandidates = [];
        foreach ($class->getProperties() as $property) {
            if (count($property->props) !== 1) {
                continue;
            }

            $propertyPromotionCandidate = $this->matchPropertyPromotionCandidate($property, $constructClassMethod);
            if ($propertyPromotionCandidate === null) {
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

            $matchedParam = $this->matchClassMethodParamByAssignedVariable($constructClassMethod, $assignedExpr);
            if ($matchedParam === null) {
                continue;
            }

            return new PropertyPromotionCandidate($property, $assign, $matchedParam);
        }

        return null;
    }

    private function matchClassMethodParamByAssignedVariable(
        ClassMethod $classMethod,
        Expr $assignedExpr
    ): ?Param {
        foreach ($classMethod->params as $param) {
            if (! $this->betterStandardPrinter->areNodesEqual($assignedExpr, $param->var)) {
                continue;
            }

            return $param;
        }

        return null;
    }
}
