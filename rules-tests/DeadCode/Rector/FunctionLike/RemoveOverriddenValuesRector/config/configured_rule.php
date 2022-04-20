<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\FunctionLike\RemoveOverriddenValuesRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RemoveOverriddenValuesRector::class);
};
