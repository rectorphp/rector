<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php81\Rector\FunctionLike\IntersectionTypesRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(IntersectionTypesRector::class);
};
