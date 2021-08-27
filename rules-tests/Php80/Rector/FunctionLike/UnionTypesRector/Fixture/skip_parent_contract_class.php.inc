<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

use Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Source\SomeContractClass;

final class SkipParentContractClass extends SomeContractClass
{
    /**
     * @param int|string $param
     */
    public function run($param)
    {
    }
}
