<?php

namespace Rector\Tests\CodeQuality\Rector\If_\ExplicitBoolCompareRector\Fixture;

final class SkipNotVariableType
{
    public function run(\stdClass $data)
    {
        if ($data->SomeProperty) {
        }
    }
}
?>
