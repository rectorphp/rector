<?php

namespace Rector\CodeQuality\Tests\Rector\If_\ExplicitBoolCompareRector\Fixture;

final class DemoFile
{
    public function run(\stdClass $data)
    {
        if ($data->SomeProperty) {
        }
    }
}
?>
