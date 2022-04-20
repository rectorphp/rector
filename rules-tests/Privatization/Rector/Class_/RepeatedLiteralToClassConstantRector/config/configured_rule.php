<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Privatization\Rector\Class_\RepeatedLiteralToClassConstantRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RepeatedLiteralToClassConstantRector::class);
};
