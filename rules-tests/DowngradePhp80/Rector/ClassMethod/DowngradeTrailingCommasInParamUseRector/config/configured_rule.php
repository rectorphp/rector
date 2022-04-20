<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(DowngradeTrailingCommasInParamUseRector::class);
};
