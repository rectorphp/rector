<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(PhpVersion::PHP_80);

    $services = $rectorConfig->services();
    $services->set(ChangeSwitchToMatchRector::class);
};
