<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

class SkipRemovingMixedReturnType
{
    /**
     * @return mixed[]
     */
    private function x(): array
    {
        return [];
    }
}
