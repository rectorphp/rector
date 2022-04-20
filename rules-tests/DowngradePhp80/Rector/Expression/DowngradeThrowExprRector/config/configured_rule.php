<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp80\Rector\Expression\DowngradeThrowExprRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(DowngradeThrowExprRector::class);
};
