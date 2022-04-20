<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php71\Rector\BooleanOr\IsIterableRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(IsIterableRector::class);
};
