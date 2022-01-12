<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

class SkipStaticParam
{
    /**
     * @param static $arg
     */
    public function go(self $arg1)
    {
    }
}
