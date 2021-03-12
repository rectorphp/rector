<?php

namespace Rector\CodeQuality\Tests\Rector\If_\ExplicitBoolCompareRector\Fixture;

final class SkipNotVariableType
{
    public function run(\stdClass $data)
    {
        if ($data->SomeProperty) {
        }
    }
}
?>
