<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php80\Rector\FunctionLike\UnionTypesRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(UnionTypesRector::class);

    $rectorConfig->phpVersion(PhpVersionFeature::UNION_TYPES);
};
