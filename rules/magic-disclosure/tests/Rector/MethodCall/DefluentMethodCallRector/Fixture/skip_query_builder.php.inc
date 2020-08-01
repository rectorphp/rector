<?php

namespace Rector\MagicDisclosure\Tests\Rector\MethodCall\DefluentMethodCallRector\Fixture;

use Rector\MagicDisclosure\Tests\Rector\MethodCall\DefluentMethodCallRector\Source\QueryBuilder;

class SkipQueryBuilder
{
    public function someFunction()
    {
        $queryBuilder = new QueryBuilder();
        $queryBuilder->addQuery()
                        ->select();
    }
}
