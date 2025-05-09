<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\NodeRemover;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
final class ReturnEmptyArrayMethodRemover
{
    public function removeClassMethodIfArrayEmpty(Class_ $class, Array_ $returnArray, string $methodName) : void
    {
        if (\count($returnArray->items) !== 0) {
            return;
        }
        foreach ($class->stmts as $key => $classStmt) {
            if (!$classStmt instanceof ClassMethod) {
                continue;
            }
            if ($classStmt->name->toString() !== $methodName) {
                continue;
            }
            unset($class->stmts[$key]);
        }
    }
}
