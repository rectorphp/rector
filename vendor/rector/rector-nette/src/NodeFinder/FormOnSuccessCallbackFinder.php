<?php

declare (strict_types=1);
namespace Rector\Nette\NodeFinder;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
final class FormOnSuccessCallbackFinder
{
    public function find(Class_ $class, Variable $form) : ?Expr
    {
        foreach ($class->getMethods() as $classMethod) {
            $stmts = $classMethod->getStmts();
            if ($stmts === null) {
                continue;
            }
            foreach ($stmts as $stmt) {
                if (!$stmt instanceof Expression) {
                    continue;
                }
                if (!$stmt->expr instanceof Assign) {
                    continue;
                }
                if (!$stmt->expr->var instanceof ArrayDimFetch) {
                    continue;
                }
                /** @var ArrayDimFetch $arrayDimFetch */
                $arrayDimFetch = $stmt->expr->var;
                if (!$this->isFormOnSuccess($arrayDimFetch, $form)) {
                    continue;
                }
                return $stmt->expr->expr;
            }
        }
        return null;
    }
    private function isFormOnSuccess(ArrayDimFetch $arrayDimFetch, Variable $form) : bool
    {
        if (!$arrayDimFetch->var instanceof PropertyFetch) {
            return \false;
        }
        if (!$arrayDimFetch->var->var instanceof Variable) {
            return \false;
        }
        if ($arrayDimFetch->var->var->name !== $form->name) {
            return \false;
        }
        if (!$arrayDimFetch->var->name instanceof Identifier) {
            return \false;
        }
        return $arrayDimFetch->var->name->name === 'onSuccess';
    }
}
