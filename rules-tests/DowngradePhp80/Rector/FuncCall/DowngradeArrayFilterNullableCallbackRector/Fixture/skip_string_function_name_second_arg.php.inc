<?php

namespace Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeArrayFilterNullableCallbackRector\Fixture;

class SkipStringFunctionNameSecondArg
{
    public function run(array $data)
    {
        $result = array_filter($data, 'strlen' );
    }
}
