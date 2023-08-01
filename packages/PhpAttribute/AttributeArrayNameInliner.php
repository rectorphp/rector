<?php

declare (strict_types=1);
namespace Rector\PhpAttribute;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Exception\NotImplementedYetException;
use RectorPrefix202308\Webmozart\Assert\Assert;
final class AttributeArrayNameInliner
{
    /**
     * @param \PhpParser\Node\Expr\Array_|mixed[] $array
     * @return Arg[]
     */
    public function inlineArrayToArgs($array) : array
    {
        if (\is_array($array)) {
            return $this->inlineArray($array);
        }
        return $this->inlineArrayNode($array);
    }
    /**
     * @return Arg[]
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
     * @param Arg[] $args
     * @return Arg[]
     */
    private function inlineArray(array $args) : array
    {
        Assert::allIsAOf($args, Arg::class);
        $newArgs = [];
        foreach ($args as $arg) {
            // matching top root array key
            if ($arg->value instanceof ArrayItem) {
                $arrayItem = $arg->value;
                if ($arrayItem->key instanceof LNumber) {
                    $newArgs[] = new Arg($arrayItem->value);
                } elseif ($arrayItem->key instanceof String_) {
                    $arrayItemString = $arrayItem->key;
                    $newArgs[] = new Arg($arrayItem->value, \false, \false, [], new Identifier($arrayItemString->value));
                } elseif (!$arrayItem->key instanceof Expr) {
                    // silent key
                    $newArgs[] = new Arg($arrayItem->value);
                } else {
                    throw new NotImplementedYetException(\get_debug_type($arrayItem->key));
                }
            }
        }
        if ($newArgs !== []) {
            return $newArgs;
        }
        return $args;
    }
}
