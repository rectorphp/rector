<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Ternary\TernaryToBooleanOrFalseToBooleanAndRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(TernaryToBooleanOrFalseToBooleanAndRector::class);
};
