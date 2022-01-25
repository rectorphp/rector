<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradeJsonDecodeNullAssociativeArgRector\Fixture;

class SkipAlreadyCastedBool
{
    function run(string $json, ?bool $associative)
    {
        $value = json_decode($json, (bool) $associative);
    }
}
