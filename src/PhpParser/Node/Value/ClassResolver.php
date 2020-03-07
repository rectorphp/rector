<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\Value;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;

final class ClassResolver
{
    public function getClassFromMethodCall(MethodCall $methodCall): ?FullyQualified
    {
        $class = null;
        $previousExpression = $methodCall->getAttribute('previousExpression');

        // [PhpParser\Node\Expr\Assign] $variable = new Class()
        if ($previousExpression instanceof Expression) {
            $class = $this->resolveFromExpression($previousExpression);
        }

        if ($previousExpression instanceof ClassMethod) {
            $class = $this->resolveFromClassMethod($previousExpression, $methodCall);
        }

        return $class;
    }

    private function resolveFromExpression(Expression $expression): ?FullyQualified
    {
        $assign = $expression->expr;
        if (! $assign instanceof Assign) {
            return null;
        }

        $new = $assign->expr;
        if (! $new instanceof New_) {
            return null;
        }

        $class = $new->class;

        return $class instanceof FullyQualified ? $class : null;
    }

    private function resolveFromClassMethod(ClassMethod $classMethod, MethodCall $methodCall): ?FullyQualified
    {
        $class = $this->tryToResolveClassMethodStmts($classMethod);

        if ($class === null) {
            $class = $this->tryToResolveClassMethodParams($classMethod, $methodCall);
        }

        return $class;
    }

    private function tryToResolveClassMethodStmts(ClassMethod $classMethod): ?FullyQualified
    {
        // $ this -> method();
        $stmts = $classMethod->stmts;
        if ($stmts === null) {
            return null;
        }

        /** @var Stmt $stmt */
        foreach ($stmts as $stmt) {
            if ($stmt->expr->var->name === 'this') {
                $class = $classMethod->name->getAttribute(ClassLike::class)->name;

                if (! $class instanceof Identifier) {
                    return null;
                }

                return new FullyQualified($class->getAttribute('className'));
            }
        }

        return null;
    }

    private function tryToResolveClassMethodParams(ClassMethod $classMethod, MethodCall $methodCall): ?FullyQualified
    {
        // $ param -> method();
        $params = $classMethod->params;
        /** @var Param $param */
        foreach ($params as $param) {
            $paramVar = $param->var;
            $methodCallVar = $methodCall->var;
            if (! $paramVar instanceof Variable || ! $methodCallVar instanceof Variable) {
                continue;
            }
            if ($paramVar->name === $methodCallVar->name) {
                $class = $param->type;
                return $class instanceof FullyQualified ? $class : null;
            }
        }

        return null;
    }
}
