<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Identical\StrEndsWithRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(StrEndsWithRector::class);
};
