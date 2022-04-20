<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php71\Rector\FuncCall\CountOnNullRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(PhpVersionFeature::COUNT_ON_NULL);
    $rectorConfig->rule(CountOnNullRector::class);
};
