<?php

declare(strict_types=1);

namespace Rector\NodeCollector\ValueObject;

use PhpParser\Node\Expr;
use Rector\Core\Validation\RectorAssert;

final class ArrayCallable
{
    public function __construct(
        private readonly Expr $callerExpr,
        private readonly string $class,
        private readonly string $method
    ) {
        RectorAssert::className($class);
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
