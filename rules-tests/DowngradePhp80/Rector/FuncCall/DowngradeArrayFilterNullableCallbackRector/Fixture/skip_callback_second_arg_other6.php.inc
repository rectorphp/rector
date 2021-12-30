<?php

namespace Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeArrayFilterNullableCallbackRector\Fixture;

class SkipCallbackSecondArgOther6
{
    public function run()
    {
        $data = ['test'];
        $filter = 'strlen';

        var_dump(
            array_filter(
                $data,
                $filter
            )
        );
    }
}
