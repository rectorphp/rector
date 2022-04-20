<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp81\Rector\ClassConst\DowngradeFinalizePublicClassConstantRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(DowngradeFinalizePublicClassConstantRector::class);
};
