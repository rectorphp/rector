<?php

declare (strict_types=1);
namespace RectorPrefix202510;

use Rector\Config\RectorConfig;
use Rector\Doctrine\Dbal36\Rector\MethodCall\MigrateQueryBuilderResetQueryPartRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(MigrateQueryBuilderResetQueryPartRector::class);
};
