<?php

namespace Rector\DowngradePhp73\Tests\Rector\FuncCall\DowngradeTrailingCommasInFunctionCallsRector\Fixture;

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
