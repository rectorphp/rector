<?php

namespace Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeArrayFilterNullableCallbackRector\Fixture;

class SkipCallbackSecondArgOther2
{
    public function run()
    {
        $data = ['test'];

        var_dump(
            array_filter(
                $data,
                ['SkipCallbackSecondArgOther2', 'filter']
            )
        );
    }

    private function filter($v)
    {
        return $v === 'test';
    }
}
