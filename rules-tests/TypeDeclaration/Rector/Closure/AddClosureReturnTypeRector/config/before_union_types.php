<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(PhpVersionFeature::UNION_TYPES - 1);
    $rectorConfig->rule(AddClosureReturnTypeRector::class);
};
