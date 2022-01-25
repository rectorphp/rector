<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradeJsonDecodeNullAssociativeArgRector\Fixture;

class SkipBoolType
{
    public function run(bool $associative)
    {
        $value = json_decode('{}', $associative);
    }
}
