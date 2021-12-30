<?php

namespace Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeArrayFilterNullableCallbackRector\Fixture;

class SkipCallbackSecondArgOther
{
    public function run()
    {
        $data = ['test'];

        var_dump(
            array_filter(
                $data,
                [$this, 'filter']
            )
        );
    }

    private function filter($v)
    {
        return $v === 'test';
    }
}
