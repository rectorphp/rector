<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Node\RemoveNonExistingVarAnnotationRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RemoveNonExistingVarAnnotationRector::class);
};
