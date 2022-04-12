<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\RemovingStatic\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(LocallyCalledStaticMethodToNonStaticRector::class);
};
