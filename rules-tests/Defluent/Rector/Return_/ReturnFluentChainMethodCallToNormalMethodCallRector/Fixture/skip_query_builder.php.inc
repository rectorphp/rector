<?php

namespace Rector\Defluent\Tests\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Defluent\Tests\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Source\QueryBuilder;

class SkipQueryBuilder
{
    public function someFunction()
    {
        $queryBuilder = new QueryBuilder();
        $queryBuilder->addQuery()->select();
    }
}
