<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradeJsonDecodeNullAssociativeArgRector\Fixture;

final class SkipAlreadyDowngraded
{
    public function run(string $json, $associative)
    {
        $value = json_decode($json, $associative === null ?: $associative);
    }
}
