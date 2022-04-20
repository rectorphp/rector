<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Rector\Config\RectorConfig;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RenamePropertyToMatchTypeRector::class);
    $rectorConfig->rule(CompleteDynamicPropertiesRector::class);
};
