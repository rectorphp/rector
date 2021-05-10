<?php

declare (strict_types=1);
namespace Rector\PhpSpecToPHPUnit;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
final class MatchersManipulator
{
    /**
     * @return string[]
     */
    public function resolveMatcherNamesFromClass(\PhpParser\Node\Stmt\Class_ $class) : array
    {
        $classMethod = $class->getMethod('getMatchers');
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return [];
        }
        if (!isset($classMethod->stmts[0])) {
            return [];
        }
        if (!$classMethod->stmts[0] instanceof \PhpParser\Node\Stmt\Return_) {
            return [];
        }
        /** @var Return_ $return */
        $return = $classMethod->stmts[0];
        if (!$return->expr instanceof \PhpParser\Node\Expr\Array_) {
            return [];
        }
        $keys = [];
        foreach ($return->expr->items as $arrayItem) {
            if ($arrayItem === null) {
                continue;
            }
            if ($arrayItem->key instanceof \PhpParser\Node\Scalar\String_) {
                $keys[] = $arrayItem->key->value;
            }
        }
        return $keys;
    }
}
