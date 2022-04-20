<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Restoration\Rector\ClassConstFetch\MissingClassConstantReferenceToStringRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(MissingClassConstantReferenceToStringRector::class);
};
