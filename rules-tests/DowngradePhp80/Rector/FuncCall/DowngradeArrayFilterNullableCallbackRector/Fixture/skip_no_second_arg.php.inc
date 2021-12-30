<?php

namespace Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeArrayFilterNullableCallbackRector\Fixture;

class SkipNoSecondArg
{
    public function run(array $data)
    {
        array_filter($data);
    }
}

?>
