<?php

namespace Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeArrayFilterNullableCallbackRector\Fixture;

class SkipCallbackSecondArgOther5
{
    public function run()
    {
        $data = ['test'];

        var_dump(
            array_filter(
                $data,
                $this->getFilter()
            )
        );
    }

    private function getFilter(): array
    {
        return ['SkipCallbackSecondArgOther5', 'filter'];
    }

    private function filter($v)
    {
        return $v === 'test';
    }
}
