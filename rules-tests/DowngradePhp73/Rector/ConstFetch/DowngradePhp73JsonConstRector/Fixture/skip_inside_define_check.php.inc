<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp73\Rector\FuncCall\DowngradePhp73JsonConstRector\Fixture;

class SkipInsideDefineCheck
{
    public function run($options)
    {
        if (\defined('JSON_THROW_ON_ERROR')) {
            $options = $options & ~\JSON_THROW_ON_ERROR;
        }
    }
}
