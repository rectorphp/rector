<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\FuncCall\IsAWithStringWithThirdArgumentRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(IsAWithStringWithThirdArgumentRector::class);
};
