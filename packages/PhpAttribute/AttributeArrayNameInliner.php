<?php

declare(strict_types=1);

namespace Rector\PhpAttribute;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use Webmozart\Assert\Assert;

final class AttributeArrayNameInliner
{
    /**
     * @param Array_|Arg[] $array
     * @return Arg[]
     */
    public function inlineArrayToArgs(Array_|array $array): array
    {
        if (is_array($array)) {
            return $this->inlineArray($array);
        }

        return $this->inlineArrayNode($array);
    }

    /**
     * @return Arg[]
     */
    private function inlineArrayNode(Array_ $array): array
    {
        $args = [];

        foreach ($array->items as $arrayItem) {
            if (! $arrayItem instanceof ArrayItem) {
                continue;
            }

            $key = $arrayItem->key;
            if ($key instanceof String_) {
                $args[] = new Arg($arrayItem->value, false, false, [], new Identifier($key->value));
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
    private function inlineArray(array $args): array
    {
        Assert::allIsAOf($args, Arg::class);

        $newArgs = [];

        foreach ($args as $arg) {
            // matching top root array key
            if ($arg->value instanceof ArrayItem) {
                $arrayItem = $arg->value;
                if ($arrayItem->key instanceof String_) {
                    $arrayItemString = $arrayItem->key;
                    $newArgs[] = new Arg($arrayItem->value, false, false, [], new Identifier($arrayItemString->value));
                }
            }
        }

        if ($newArgs !== []) {
            return $newArgs;
        }

        return $args;
    }
}
