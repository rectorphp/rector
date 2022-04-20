<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php71\Rector\TryCatch\MultiExceptionCatchRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(MultiExceptionCatchRector::class);
};
