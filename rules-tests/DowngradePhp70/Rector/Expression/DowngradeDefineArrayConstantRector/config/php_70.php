<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DowngradePhp70\Rector\Expression\DowngradeDefineArrayConstantRector;

return static function (RectorConfig $rectorConfig): void {
    $parameters = $rectorConfig->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersionFeature::SCALAR_TYPES - 1);

    $services = $rectorConfig->services();
    $services->set(DowngradeDefineArrayConstantRector::class);
};
