<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector;

return static function (RectorConfig $rectorConfig): void {
    $parameters = $rectorConfig->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_80);

    $services = $rectorConfig->services();
    $services->set(RemoveUnusedVariableInCatchRector::class);
};
