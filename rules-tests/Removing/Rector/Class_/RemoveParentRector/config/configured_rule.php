<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Removing\Rector\Class_\RemoveParentRector;
use Rector\Tests\Removing\Rector\Class_\RemoveParentRector\Source\ParentTypeToBeRemoved;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(RemoveParentRector::class, [ParentTypeToBeRemoved::class]);
};
