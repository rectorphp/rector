<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Strict\Rector\Ternary\DisallowedShortTernaryRuleFixerRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(DisallowedShortTernaryRuleFixerRector::class);
};
