<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;

return static function (RectorConfig $rectorConfig): void {
    $parameters = $rectorConfig->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_81);

    $services = $rectorConfig->services();
    $services->set(ReturnNeverTypeRector::class);
};
