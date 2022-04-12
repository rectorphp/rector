<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector;
use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersionFeature;

return static function (RectorConfig $rectorConfig): void {
    $parameters = $rectorConfig->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersionFeature::TYPED_PROPERTIES);

    $services = $rectorConfig->services();
    $services->set(IssetOnPropertyObjectToPropertyExistsRector::class);
};
