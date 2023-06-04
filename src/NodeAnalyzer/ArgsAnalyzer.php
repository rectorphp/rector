<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Identifier;
final class ArgsAnalyzer
{
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
