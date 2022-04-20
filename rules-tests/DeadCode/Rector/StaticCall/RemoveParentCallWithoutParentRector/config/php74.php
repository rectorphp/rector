<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(PhpVersion::PHP_74);
    $rectorConfig->rule(RemoveParentCallWithoutParentRector::class);
};
