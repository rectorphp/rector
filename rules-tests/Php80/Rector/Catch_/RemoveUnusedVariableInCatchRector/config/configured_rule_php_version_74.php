<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(PhpVersion::PHP_74);

    $services = $rectorConfig->services();
    $services->set(RemoveUnusedVariableInCatchRector::class);
};
