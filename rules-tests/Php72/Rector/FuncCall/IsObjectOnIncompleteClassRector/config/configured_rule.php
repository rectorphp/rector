<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php72\Rector\FuncCall\IsObjectOnIncompleteClassRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(IsObjectOnIncompleteClassRector::class);
};
