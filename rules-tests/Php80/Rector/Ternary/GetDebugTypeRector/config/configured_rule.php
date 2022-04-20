<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Ternary\GetDebugTypeRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(GetDebugTypeRector::class);
};
