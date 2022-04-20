<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php74\Rector\Property\TypedPropertyRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(TypedPropertyRector::class);

    $rectorConfig->phpVersion(PhpVersionFeature::INTERSECTION_TYPES);
};
