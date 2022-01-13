<?php

namespace Rector\Tests\Php80\Rector\FuncCall\NullToStrictStringFuncCallArgRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;

final class SkipReassignCast
{
    /**
     * @param LNumber|DNumber $node
     */
    public function refactor(Node $node)
    {
        $node->value = (string) $node->value;
        if ($node instanceof DNumber && ! \str_contains($node->value, '.')) {

        }
    }
}
