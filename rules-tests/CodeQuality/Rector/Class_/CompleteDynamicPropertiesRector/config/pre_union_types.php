<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersionFeature;

return static function (RectorConfig $rectorConfig): void {
    $parameters = $rectorConfig->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersionFeature::UNION_TYPES - 1);

    $services = $rectorConfig->services();
    $services->set(CompleteDynamicPropertiesRector::class);
};
