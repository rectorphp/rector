<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Identifier;
final class ArgsAnalyzer
{
    /**
     * @api
     * @deprecated Use $node->getArgs()[x] instead
     * @param Arg[] $args
     */
    public function isArgInstanceInArgsPosition(array $args, int $position) : bool
    {
        return isset($args[$position]);
    }
    /**
     * @api
     * @param Arg[] $args
     * @param int[] $positions
     * @deprecated use count($node->getArgs() < X instead
     */
    public function isArgsInstanceInArgsPositions(array $args, array $positions) : bool
    {
        foreach ($positions as $position) {
            if (!isset($args[$position])) {
                return \false;
            }
        }
        return \true;
    }
    /**
     * @param mixed[]|Arg[] $args
     */
    public function hasNamedArg(array $args) : bool
    {
        foreach ($args as $arg) {
            if (!$arg instanceof Arg) {
                continue;
            }
            if ($arg->name instanceof Identifier) {
                return \true;
            }
        }
        return \false;
    }
}
