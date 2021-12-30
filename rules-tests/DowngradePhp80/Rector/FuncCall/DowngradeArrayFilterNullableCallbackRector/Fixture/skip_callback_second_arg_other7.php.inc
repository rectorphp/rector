<?php

namespace Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeArrayFilterNullableCallbackRector\Fixture;

class SkipCallbackSecondArgOther7
{
    public function run(string $filter)
    {
        $data = ['test'];

        var_dump(
            array_filter(
                $data,
                $filter
            )
        );
    }
}
