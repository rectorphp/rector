<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Assign\SplitDoubleAssignRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(SplitDoubleAssignRector::class);
    $rectorConfig->rule(RemoveUnusedPrivatePropertyRector::class);
};
