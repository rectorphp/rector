<?php

declare (strict_types=1);
namespace Rector\Symfony;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Symfony\ValueObject\ConstantNameAndValue;
use RectorPrefix20211020\Stringy\Stringy;
final class ConstantNameAndValueMatcher
{
    /**
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(\Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function matchFromArg(\PhpParser\Node\Arg $arg, string $prefixForNumeric) : ?\Rector\Symfony\ValueObject\ConstantNameAndValue
    {
        if ($arg->value instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            return null;
        }
        $argumentValue = $this->valueResolver->getValue($arg->value);
        if (!\is_string($argumentValue)) {
            return null;
        }
        $stringy = new \RectorPrefix20211020\Stringy\Stringy($argumentValue);
        $constantName = (string) $stringy->underscored()->toUpperCase();
        if (!\ctype_alpha($constantName[0])) {
            $constantName = $prefixForNumeric . $constantName;
        }
        return new \Rector\Symfony\ValueObject\ConstantNameAndValue($constantName, $argumentValue);
    }
}
