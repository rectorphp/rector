<?php

namespace Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeArrayFilterNullableCallbackRector\Fixture;

class SkipCallbackSecondArgWithClosure
{
    public function run(array $data)
    {
        array_filter($data, function ($v, $k) { return ! empty ($v); }, ARRAY_FILTER_USE_BOTH);
    }
}
