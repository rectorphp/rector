<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\Class_\ClassConstantToSelfClassRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ClassConstantToSelfClassRector::class);
};
