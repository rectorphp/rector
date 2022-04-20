<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php73\Rector\String_\SensitiveHereNowDocRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(SensitiveHereNowDocRector::class);
};
