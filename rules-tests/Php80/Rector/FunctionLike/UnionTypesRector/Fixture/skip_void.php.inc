<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

class SkipVoid
{
    /**
     * @return int|void
     */
    public function go()
    {
    }
}
