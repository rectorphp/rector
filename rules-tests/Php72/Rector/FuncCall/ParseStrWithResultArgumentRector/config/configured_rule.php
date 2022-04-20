<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php72\Rector\FuncCall\ParseStrWithResultArgumentRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ParseStrWithResultArgumentRector::class);
};
