<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\FuncCall\AddPregQuoteDelimiterRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(AddPregQuoteDelimiterRector::class);
};
