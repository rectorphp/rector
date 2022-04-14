<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(TypedPropertyFromAssignsRector::class, [
        TypedPropertyFromAssignsRector::INLINE_PUBLIC => true,
    ]);

    $rectorConfig->phpVersion(PhpVersionFeature::INTERSECTION_TYPES);
};
