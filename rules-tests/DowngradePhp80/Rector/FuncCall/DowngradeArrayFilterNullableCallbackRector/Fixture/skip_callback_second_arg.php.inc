<?php

namespace Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeArrayFilterNullableCallbackRector\Fixture;

class SkipCallbackSecondArg
{
    public function run(array $data)
    {
        array_filter($data, fn ($v, $k) => ! empty ($v), ARRAY_FILTER_USE_BOTH);
    }
}
