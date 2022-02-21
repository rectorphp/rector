<?php

declare(strict_types=1);

namespace Rector\Php81\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar;
use Rector\Core\NodeAnalyzer\ExprAnalyzer;

final class ComplexNewAnalyzer
{
    public function __construct(
        private readonly ExprAnalyzer $exprAnalyzer
    ) {
    }

    public function isDynamic(New_ $new): bool
    {
        if (! $new->class instanceof FullyQualified) {
            return true;
        }

        $args = $new->getArgs();

        foreach ($args as $arg) {
            $value = $arg->value;

            if ($this->isAllowedNew($value)) {
                continue;
            }

            if ($value instanceof Array_ && $this->isAllowedArray($value)) {
                continue;
            }

            if ($value instanceof Scalar) {
                continue;
            }

            if ($this->isAllowedConstFetchOrClassConstFeth($value)) {
                continue;
            }

            return true;
        }

        return false;
    }

    private function isAllowedConstFetchOrClassConstFeth(Expr $expr): bool
    {
        if (! in_array($expr::class, [ConstFetch::class, ClassConstFetch::class], true)) {
            return false;
        }

        if ($expr instanceof ClassConstFetch) {
            return $expr->class instanceof Name && $expr->name instanceof Identifier;
        }

        return true;
    }

    private function isAllowedNew(Expr $expr): bool
    {
        if ($expr instanceof New_) {
            return ! $this->isDynamic($expr);
        }

        return false;
    }

    private function isAllowedArray(Array_ $array): bool
    {
        if (! $this->exprAnalyzer->isDynamicArray($array)) {
            return true;
        }

        $arrayItems = $array->items;
        foreach ($arrayItems as $arrayItem) {
            if (! $arrayItem instanceof ArrayItem) {
                continue;
            }

            if (! $arrayItem->value instanceof New_) {
                return false;
            }

            if ($this->isDynamic($arrayItem->value)) {
                return false;
            }
        }

        return true;
    }
}
