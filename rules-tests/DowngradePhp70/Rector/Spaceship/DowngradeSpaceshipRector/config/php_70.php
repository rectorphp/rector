<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DowngradePhp70\Rector\Spaceship\DowngradeSpaceshipRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(PhpVersionFeature::SCALAR_TYPES - 1);

    $services = $rectorConfig->services();
    $services->set(DowngradeSpaceshipRector::class);
};
