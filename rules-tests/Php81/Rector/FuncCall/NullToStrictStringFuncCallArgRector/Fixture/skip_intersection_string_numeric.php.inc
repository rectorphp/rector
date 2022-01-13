<?php

namespace Rector\Tests\Php80\Rector\FuncCall\NullToStrictStringFuncCallArgRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;

final class SkipIntersectionStringNumeric
{
    /**
     * @param LNumber|DNumber $node
     */
    public function refactor(Node $node)
    {
        $numericValueAsString = (string) $node->value;

        if (\str_contains($numericValueAsString, '.')) {
            [$mainPart, $decimalPart] = explode('.', $numericValueAsString);
        }
    }
}
