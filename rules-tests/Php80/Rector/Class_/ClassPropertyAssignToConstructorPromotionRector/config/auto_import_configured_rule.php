<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->importNames();

    $services = $rectorConfig->services();
    $services->set(ClassPropertyAssignToConstructorPromotionRector::class);
};
