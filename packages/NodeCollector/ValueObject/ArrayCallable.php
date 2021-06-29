<?php

declare(strict_types=1);

namespace Rector\NodeCollector\ValueObject;

use PhpParser\Node\Expr;

final class ArrayCallable
{
    public function __construct(
        private Expr $callerExpr,
        private string $class,
        private string $method
    ) {
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getCallerExpr(): Expr
    {
        return $this->callerExpr;
    }
}
