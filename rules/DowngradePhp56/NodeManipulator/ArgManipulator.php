<?php

declare (strict_types=1);
namespace Rector\DowngradePhp56\NodeManipulator;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
final class ArgManipulator
{
    /**
     * @param Arg[] $args
     */
    public function hasUnpackedArg(array $args) : bool
    {
        foreach ($args as $arg) {
            if ($arg->unpack) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param Arg[] $args
     * @return Arg[]
     */
    public function unpack(array $args) : array
    {
        $unpackedArgList = new \Rector\DowngradePhp56\NodeManipulator\UnpackedArgList($args);
        return $unpackedArgList->toArray();
    }
    /**
     * @param Arg[] $args
     */
    public function canBeInlined(array $args) : bool
    {
        foreach ($args as $arg) {
            if (!$arg->unpack) {
                continue;
            }
            if ($arg->value instanceof \PhpParser\Node\Expr\Array_) {
                continue;
            }
            return \false;
        }
        return \true;
    }
}
