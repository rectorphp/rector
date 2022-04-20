<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Assign\SplitDoubleAssignRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(SplitDoubleAssignRector::class);
};
