<?php

declare(strict_types=1);

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
    public function resolveMatcherNamesFromClass(Class_ $class): array
    {
        $classMethod = $class->getMethod('getMatchers');
        if (! $classMethod instanceof ClassMethod) {
            return [];
        }

        if (! isset($classMethod->stmts[0])) {
            return [];
        }

        if (! $classMethod->stmts[0] instanceof Return_) {
            return [];
        }

        /** @var Return_ $return */
        $return = $classMethod->stmts[0];
        if (! $return->expr instanceof Array_) {
            return [];
        }

        $keys = [];
        foreach ($return->expr->items as $item) {
            if ($item === null) {
                continue;
            }

            if ($item->key instanceof String_) {
                $keys[] = $item->key->value;
            }
        }

        return $keys;
    }
}
