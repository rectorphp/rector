<?php

namespace Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeArrayFilterNullableCallbackRector\Fixture;

class SkipNamedArg
{
    public function run(array $data)
    {
        array_filter(array: $data, callback: null);
    }
}
