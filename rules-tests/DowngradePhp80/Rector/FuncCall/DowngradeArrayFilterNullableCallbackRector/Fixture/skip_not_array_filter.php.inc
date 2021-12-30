<?php

namespace Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeArrayFilterNullableCallbackRector\Fixture;

class SkipNotArrayFilter
{
    public function run()
    {
        strlen('test');
    }
}
