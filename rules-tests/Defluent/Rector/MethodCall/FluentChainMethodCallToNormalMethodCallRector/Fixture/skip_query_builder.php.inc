<?php

namespace Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Tests\Defluent\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source\QueryBuilder;

class SkipQueryBuilder
{
    public function someFunction()
    {
        $queryBuilder = new QueryBuilder();
        $queryBuilder->addQuery()
                        ->select();
    }
}
