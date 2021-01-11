<?php

declare(strict_types=1);

namespace Rector\SymfonyCodeQuality;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\Util\StaticRectorStrings;
use Rector\SymfonyCodeQuality\ValueObject\ConstantNameAndValue;

final class ConstantNameAndValueMatcher
{
    /**
     * @var ValueResolver
     */
    private $valueResolver;

    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }

    public function matchFromArg(Arg $arg): ?ConstantNameAndValue
    {
        if ($arg->value instanceof ClassConstFetch) {
            return null;
        }

        $argumentValue = $this->valueResolver->getValue($arg->value);
        if (! is_string($argumentValue)) {
            return null;
        }

        $constantName = StaticRectorStrings::camelCaseToConstant($argumentValue);
        return new ConstantNameAndValue($constantName, $argumentValue);
    }
}
