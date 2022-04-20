<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(PhpVersionFeature::PROPERTY_PROMOTION);
    $rectorConfig->rule(RenamePropertyToMatchTypeRector::class);
};
