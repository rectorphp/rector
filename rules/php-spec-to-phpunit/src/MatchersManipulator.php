<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Return_;

final class MatchersManipulator
{
    /**
     * @return string[]
     */
    public function resolveMatcherNamesFromClass(Class_ $class): array
    {
        $getMatchersMethod = $class->getMethod('getMatchers');
        if ($getMatchersMethod === null) {
            return [];
        }

        if (! isset($getMatchersMethod->stmts[0])) {
            return [];
        }

        if (! $getMatchersMethod->stmts[0] instanceof Return_) {
            return [];
        }

        /** @var Return_ $return */
        $return = $getMatchersMethod->stmts[0];
        if (! $return->expr instanceof Array_) {
            return [];
        }

        $keys = [];
        foreach ($return->expr->items as $arrayItem) {
            if ($arrayItem->key instanceof String_) {
                $keys[] = $arrayItem->key->value;
            }
        }

        return $keys;
    }
}
