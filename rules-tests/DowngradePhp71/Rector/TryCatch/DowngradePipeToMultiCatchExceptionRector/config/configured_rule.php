<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DowngradePhp71\Rector\TryCatch\DowngradePipeToMultiCatchExceptionRector;

return static function (RectorConfig $rectorConfig): void {
    $parameters = $rectorConfig->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersionFeature::MULTI_EXCEPTION_CATCH - 1);

    $services = $rectorConfig->services();
    $services->set(DowngradePipeToMultiCatchExceptionRector::class);
};
