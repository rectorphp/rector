<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Identifier;
use PhpParser\Node\VariadicPlaceholder;
final class ArgsAnalyzer
{
    /**
     * @param Arg[]|VariadicPlaceholder[] $args
     */
    public function isArgInstanceInArgsPosition(array $args, int $position) : bool
    {
        if (!isset($args[$position])) {
            return \false;
        }
        return $args[$position] instanceof \PhpParser\Node\Arg;
    }
    /**
     * @param Arg[]|VariadicPlaceholder[] $args
     * @param int[] $positions
     */
    public function isArgsInstanceInArgsPositions(array $args, array $positions) : bool
    {
        foreach ($positions as $position) {
            if (!isset($args[$position])) {
                return \false;
            }
            if ($args[$position] instanceof \PhpParser\Node\Arg) {
                continue;
            }
            return \false;
        }
        return \true;
    }
    /**
     * @param mixed[]|Arg[] $args
     */
    public function hasNamedArg(array $args) : bool
    {
        foreach ($args as $arg) {
            if (!$arg instanceof \PhpParser\Node\Arg) {
                continue;
            }
            if ($arg->name instanceof \PhpParser\Node\Identifier) {
                return \true;
            }
        }
        return \false;
    }
}
