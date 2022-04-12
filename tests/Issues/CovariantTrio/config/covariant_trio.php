<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp70\Rector\Coalesce\DowngradeNullCoalesceRector;
use Rector\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector;
use Rector\DowngradePhp73\Rector\FuncCall\DowngradeArrayKeyFirstLastRector;

return static function (RectorConfig $rectorConfig): void {
    $parameters = $rectorConfig->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_70);

    $services = $rectorConfig->services();
    $services->set(DowngradeArrayKeyFirstLastRector::class);
    $services->set(DowngradeScalarTypeDeclarationRector::class);
    $services->set(DowngradeNullCoalesceRector::class);
};
