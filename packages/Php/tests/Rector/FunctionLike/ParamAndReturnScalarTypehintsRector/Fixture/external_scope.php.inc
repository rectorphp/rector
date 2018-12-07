<?php

namespace Rector\Php\Tests\Rector\FunctionLike\ParamAndReturnScalarTypehintsRector\Fixture;

use Rector\Php\Tests\Rector\FunctionLike\ParamAndReturnScalarTypehintsRector\Source\SniffInterface;

final class CoolSniff implements SniffInterface
{
    /**
     * @param int $position
     */
    public function process(string $file, $position)
    {
    }
}
