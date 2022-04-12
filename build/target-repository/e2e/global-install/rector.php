<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([__DIR__ . '/src/']);

    $services = $rectorConfig->services();
    $services->set(MakeInheritedMethodVisibilitySameAsParentRector::class);
};
