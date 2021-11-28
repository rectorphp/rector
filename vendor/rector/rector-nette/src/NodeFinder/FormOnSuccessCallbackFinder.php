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
    public function find(\PhpParser\Node\Stmt\Class_ $class, \PhpParser\Node\Expr\Variable $form) : ?\PhpParser\Node\Expr
    {
        foreach ($class->getMethods() as $classMethod) {
            $stmts = $classMethod->getStmts();
            if ($stmts === null) {
                continue;
            }
            foreach ($stmts as $stmt) {
                if (!$stmt instanceof \PhpParser\Node\Stmt\Expression) {
                    continue;
                }
                if (!$stmt->expr instanceof \PhpParser\Node\Expr\Assign) {
                    continue;
                }
                if (!$stmt->expr->var instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
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
    private function isFormOnSuccess(\PhpParser\Node\Expr\ArrayDimFetch $arrayDimFetch, \PhpParser\Node\Expr\Variable $form) : bool
    {
        if (!$arrayDimFetch->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return \false;
        }
        if (!$arrayDimFetch->var->var instanceof \PhpParser\Node\Expr\Variable) {
            return \false;
        }
        if ($arrayDimFetch->var->var->name !== $form->name) {
            return \false;
        }
        if (!$arrayDimFetch->var->name instanceof \PhpParser\Node\Identifier) {
            return \false;
        }
        if ($arrayDimFetch->var->name->name !== 'onSuccess') {
            return \false;
        }
        return \true;
    }
}
