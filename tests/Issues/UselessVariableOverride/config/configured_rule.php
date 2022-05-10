<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\FunctionLike\RemoveOverriddenValuesRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(SimplifyUselessVariableRector::class);
    $rectorConfig->rule(RemoveOverriddenValuesRector::class);
};
