<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

class SkipRemovingNullableMixedReturnType
{
    /**
     * @return mixed[]|null
     */
    private function x(bool $returnNull): ?array
    {
        return $returnNull ? null : [];
    }
}
