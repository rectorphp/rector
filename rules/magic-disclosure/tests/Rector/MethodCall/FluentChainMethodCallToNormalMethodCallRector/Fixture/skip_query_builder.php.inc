<?php

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\MagicDisclosure\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\Source\QueryBuilder;

class SkipQueryBuilder
{
    public function someFunction()
    {
        $queryBuilder = new QueryBuilder();
        $queryBuilder->addQuery()
                        ->select();
    }
}
