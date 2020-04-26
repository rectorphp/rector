<?php

declare(strict_types=1);

namespace Rector\CodeQuality;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\Value\ValueResolver;

final class CompactConverter
{
    /**
     * @var ValueResolver
     */
    private $valueResolver;

    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }

    public function hasAllArgumentsNamed(FuncCall $funcCall): bool
    {
        foreach ($funcCall->args as $arg) {
            /** @var string|null $variableName */
            $variableName = $this->valueResolver->getValue($arg->value);
            if (! is_string($variableName)) {
                return false;
            }
        }

        return true;
    }

    public function convertToArray(FuncCall $funcCall): Array_
    {
        $array = new Array_();

        foreach ($funcCall->args as $arg) {
            /** @var string|null $variableName */
            $variableName = $this->valueResolver->getValue($arg->value);
            if (! is_string($variableName)) {
                throw new ShouldNotHappenException();
            }

            $array->items[] = new ArrayItem(new Variable($variableName), new String_($variableName));
        }

        return $array;
    }
}
