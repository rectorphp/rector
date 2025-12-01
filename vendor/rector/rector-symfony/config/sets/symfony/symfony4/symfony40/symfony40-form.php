<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony40\Rector\MethodCall\FormIsValidRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([FormIsValidRector::class]);
};
