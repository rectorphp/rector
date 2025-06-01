<?php

declare (strict_types=1);
namespace Rector\PhpAttribute;

use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use RectorPrefix202506\Webmozart\Assert\Assert;
final class AttributeArrayNameInliner
{
    /**
     * @param Array_|list<Arg> $array
     * @return list<Arg>
     */
    public function inlineArrayToArgs($array) : array
    {
        if (\is_array($array)) {
            return $this->inlineArray($array);
        }
        return $this->inlineArrayNode($array);
    }
    /**
     * @return list<Arg>
     */
    private function inlineArrayNode(Array_ $array) : array
    {
        $args = [];
        foreach ($array->items as $arrayItem) {
            if (!$arrayItem instanceof ArrayItem) {
                continue;
            }
            if ($arrayItem->key instanceof String_) {
                $string = $arrayItem->key;
                $argumentName = new Identifier($string->value);
                $args[] = new Arg($arrayItem->value, \false, \false, [], $argumentName);
            } else {
                $args[] = new Arg($arrayItem->value);
            }
        }
        return $args;
    }
    /**
     * @param list<Arg> $args
     * @return list<Arg>
     */
    private function inlineArray(array $args) : array
    {
        Assert::allIsAOf($args, Arg::class);
        foreach ($args as $arg) {
            if ($arg->value instanceof String_ && \is_numeric($arg->value->value)) {
                // use equal over identical on purpose to verify if it is an integer
                if ((float) $arg->value->value == (int) $arg->value->value) {
                    $arg->value = new Int_((int) $arg->value->value);
                } else {
                    $arg->value = new Float_((float) $arg->value->value);
                }
            }
        }
        return $args;
    }
}
