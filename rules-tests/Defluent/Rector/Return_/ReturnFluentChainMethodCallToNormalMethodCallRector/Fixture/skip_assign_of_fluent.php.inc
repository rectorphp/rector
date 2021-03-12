<?php

namespace Rector\Tests\Defluent\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Fixture;

use Rector\Tests\Defluent\Rector\Return_\ReturnFluentChainMethodCallToNormalMethodCallRector\Source\Row;

final class SkipAssignOfFluent
{
    public function run(Row $row)
    {
        $cell = $row->addCell()->setName(2);
        return $cell;
    }
}
