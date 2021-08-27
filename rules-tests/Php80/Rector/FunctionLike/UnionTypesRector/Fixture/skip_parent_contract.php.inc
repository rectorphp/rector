<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

use Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Source\SomeContract;

final class SkipParentContract implements SomeContract
{
    /**
     * @param int|string $param
     */
    public function run($param)
    {
    }
}
