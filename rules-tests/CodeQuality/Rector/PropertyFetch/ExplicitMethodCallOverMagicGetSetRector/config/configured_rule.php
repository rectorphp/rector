<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ExplicitMethodCallOverMagicGetSetRector::class);
};
