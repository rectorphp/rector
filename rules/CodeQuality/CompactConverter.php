<?php

declare (strict_types=1);
namespace Rector\CodeQuality;

use PhpParser\Node\Arg;
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
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(\Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function hasAllArgumentsNamed(\PhpParser\Node\Expr\FuncCall $funcCall) : bool
    {
        foreach ($funcCall->args as $arg) {
            // VariadicPlaceholder doesn't has name, so it return false directly
            if (!$arg instanceof \PhpParser\Node\Arg) {
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
    public function convertToArray(\PhpParser\Node\Expr\FuncCall $funcCall) : \PhpParser\Node\Expr\Array_
    {
        $array = new \PhpParser\Node\Expr\Array_();
        foreach ($funcCall->args as $arg) {
            if (!$arg instanceof \PhpParser\Node\Arg) {
                throw new \Rector\Core\Exception\ShouldNotHappenException();
            }
            /** @var string|null $variableName */
            $variableName = $this->valueResolver->getValue($arg->value);
            if (!\is_string($variableName)) {
                throw new \Rector\Core\Exception\ShouldNotHappenException();
            }
            $array->items[] = new \PhpParser\Node\Expr\ArrayItem(new \PhpParser\Node\Expr\Variable($variableName), new \PhpParser\Node\Scalar\String_($variableName));
        }
        return $array;
    }
}
