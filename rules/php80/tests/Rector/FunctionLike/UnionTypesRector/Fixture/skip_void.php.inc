<?php

namespace Rector\Php80\Tests\Rector\FunctionLike\UnionTypesRector\Fixture;

class SkipVoid
{
    /**
     * @return int|void
     */
    public function go()
    {
    }
}
