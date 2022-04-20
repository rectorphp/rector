<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php73\Rector\FuncCall\ArrayKeyFirstLastRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ArrayKeyFirstLastRector::class);
};
