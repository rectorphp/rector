<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(SimplifyRegexPatternRector::class);
};
