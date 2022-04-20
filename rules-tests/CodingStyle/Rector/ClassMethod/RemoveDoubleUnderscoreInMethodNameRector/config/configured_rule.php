<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\ClassMethod\RemoveDoubleUnderscoreInMethodNameRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RemoveDoubleUnderscoreInMethodNameRector::class);
};
