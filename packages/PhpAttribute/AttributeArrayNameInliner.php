<?php

declare (strict_types=1);
namespace Rector\PhpAttribute;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Exception\NotImplementedYetException;
use RectorPrefix20220531\Webmozart\Assert\Assert;
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
    private function inlineArrayNode(\PhpParser\Node\Expr\Array_ $array) : array
    {
        $args = [];
        foreach ($array->items as $arrayItem) {
            if (!$arrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            $key = $arrayItem->key;
            if ($key instanceof \PhpParser\Node\Scalar\String_) {
                $args[] = new \PhpParser\Node\Arg($arrayItem->value, \false, \false, [], new \PhpParser\Node\Identifier($key->value));
            } else {
                $args[] = new \PhpParser\Node\Arg($arrayItem->value);
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
        \RectorPrefix20220531\Webmozart\Assert\Assert::allIsAOf($args, \PhpParser\Node\Arg::class);
        $newArgs = [];
        foreach ($args as $arg) {
            // matching top root array key
            if ($arg->value instanceof \PhpParser\Node\Expr\ArrayItem) {
                $arrayItem = $arg->value;
                if ($arrayItem->key instanceof \PhpParser\Node\Scalar\String_) {
                    $arrayItemString = $arrayItem->key;
                    $newArgs[] = new \PhpParser\Node\Arg($arrayItem->value, \false, \false, [], new \PhpParser\Node\Identifier($arrayItemString->value));
                } elseif ($arrayItem->key === null) {
                    // silent key
                    $newArgs[] = new \PhpParser\Node\Arg($arrayItem->value);
                } else {
                    throw new \Rector\Core\Exception\NotImplementedYetException(\get_debug_type($arrayItem->key));
                }
            }
        }
        if ($newArgs !== []) {
            return $newArgs;
        }
        return $args;
    }
}
