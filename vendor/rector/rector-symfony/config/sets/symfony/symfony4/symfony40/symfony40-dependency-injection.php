<?php

declare (strict_types=1);
namespace RectorPrefix202510;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony40\Rector\MethodCall\ContainerBuilderCompileEnvArgumentRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([ContainerBuilderCompileEnvArgumentRector::class]);
};
