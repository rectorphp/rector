<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(DowngradeCovariantReturnTypeRector::class);
};
