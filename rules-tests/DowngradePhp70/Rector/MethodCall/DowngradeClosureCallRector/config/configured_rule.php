<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp70\Rector\MethodCall\DowngradeClosureCallRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(DowngradeClosureCallRector::class);
};
