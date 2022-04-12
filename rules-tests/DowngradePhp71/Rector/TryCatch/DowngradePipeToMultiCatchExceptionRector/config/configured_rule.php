<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DowngradePhp71\Rector\TryCatch\DowngradePipeToMultiCatchExceptionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(PhpVersionFeature::MULTI_EXCEPTION_CATCH - 1);

    $services = $rectorConfig->services();
    $services->set(DowngradePipeToMultiCatchExceptionRector::class);
};
