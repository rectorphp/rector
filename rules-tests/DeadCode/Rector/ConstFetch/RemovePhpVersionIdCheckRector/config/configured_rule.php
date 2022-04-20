<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DeadCode\Rector\ConstFetch\RemovePhpVersionIdCheckRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(RemovePhpVersionIdCheckRector::class, [PhpVersion::PHP_80]);
};
