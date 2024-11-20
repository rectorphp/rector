<?php

declare (strict_types=1);
namespace Rector\CodeQuality;

use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Node\Value\ValueResolver;
final class CompactConverter
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function hasAllArgumentsNamed(FuncCall $funcCall) : bool
    {
        foreach ($funcCall->args as $arg) {
            // VariadicPlaceholder doesn't has name, so it return false directly
            if (!$arg instanceof Arg) {
                return \false;
            }
            /** @var string|null $variableName */
            $variableName = $this->valueResolver->getValue($arg->value);
            if (!\is_string($variableName)) {
                return \false;
            }
        }
        return \true;
    }
    public function convertToArray(FuncCall $funcCall) : Array_
    {
        $array = new Array_();
        foreach ($funcCall->args as $arg) {
            if (!$arg instanceof Arg) {
                throw new ShouldNotHappenException();
            }
            /** @var string|null $variableName */
            $variableName = $this->valueResolver->getValue($arg->value);
            if (!\is_string($variableName)) {
                throw new ShouldNotHappenException();
            }
            $array->items[] = new ArrayItem(new Variable($variableName), new String_($variableName));
        }
        return $array;
    }
}
