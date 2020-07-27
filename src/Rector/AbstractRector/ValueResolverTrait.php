<?php

declare(strict_types=1);

namespace Rector\Core\Rector\AbstractRector;

use PhpParser\Node\Expr;
use Rector\Core\PhpParser\Node\Value\ValueResolver;

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
    public function autowireValueResolverTrait(ValueResolver $valueResolver): void
    {
        $this->valueResolver = $valueResolver;
    }

    /**
     * @return mixed|mixed[]
     */
    protected function getValue(Expr $expr, bool $resolvedClassReference = false)
    {
        return $this->valueResolver->getValue($expr, $resolvedClassReference);
    }

    /**
     * @param mixed $expectedValue
     */
    protected function isValue(Expr $expr, $expectedValue): bool
    {
        return $this->getValue($expr) === $expectedValue;
    }

    /**
     * @param mixed[] $expectedValues
     */
    protected function isValues(Expr $expr, array $expectedValues): bool
    {
        foreach ($expectedValues as $expectedValue) {
            if ($this->isValue($expr, $expectedValue)) {
                return true;
            }
        }

        return false;
    }
}
