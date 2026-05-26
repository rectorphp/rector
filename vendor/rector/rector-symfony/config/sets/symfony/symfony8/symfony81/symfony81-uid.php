<?php

declare (strict_types=1);
namespace RectorPrefix202605;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony81\Rector\StaticCall\AddFormatArgumentToIsValidRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([AddFormatArgumentToIsValidRector::class]);
};
