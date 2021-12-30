<?php

namespace Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeArrayFilterNullableCallbackRector\Fixture;

class SkipCallbackSecondArgOther3
{
    public function run()
    {
        $data = ['test'];
        $filter = function ($k) { return $k === 0; };

        var_dump(
            array_filter(
                $data,
                $filter,
                ARRAY_FILTER_USE_KEY
            )
        );
    }

    private function filter($v)
    {
        return $v === 'test';
    }
}
