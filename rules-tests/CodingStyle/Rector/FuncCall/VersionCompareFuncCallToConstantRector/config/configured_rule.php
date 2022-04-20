<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\FuncCall\VersionCompareFuncCallToConstantRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(VersionCompareFuncCallToConstantRector::class);
};
