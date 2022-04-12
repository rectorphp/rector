<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Removing\Rector\Class_\RemoveParentRector;
use Rector\Tests\Removing\Rector\Class_\RemoveParentRector\Source\ParentTypeToBeRemoved;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(RemoveParentRector::class)
        ->configure([ParentTypeToBeRemoved::class]);
};
