<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RenameForeachValueVariableToMatchMethodCallReturnTypeRector::class);
};
