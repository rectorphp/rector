<?php

namespace Rector\Tests\DowngradePhp73\Rector\FuncCall\DowngradeTrailingCommasInFunctionCallsRector\Fixture;

final class SkipNone
{
    public function run($key, $value)
    {
        $this->print(
            $key,
            $value
        );
    }
}
