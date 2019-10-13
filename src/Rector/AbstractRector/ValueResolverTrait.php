<?php

declare(strict_types=1);

namespace Rector\Rector\AbstractRector;

use PhpParser\Node\Expr;
use Rector\PhpParser\Node\Value\ValueResolver;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait ValueResolverTrait
{
    /**
     * @var ValueResolver
     */
    private $valueResolver;

    /**
     * @required
     */
    public function setValueResolver(ValueResolver $valueResolver): void
    {
        $this->valueResolver = $valueResolver;
    }

    /**
     * @return mixed
     */
    protected function getValue(Expr $expr)
    {
        return $this->valueResolver->getValue($expr);
    }

    /**
     * @param mixed $expectedValue
     */
    protected function isValue(Expr $expr, $expectedValue): bool
    {
        return $this->getValue($expr) === $expectedValue;
    }
}
