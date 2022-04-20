<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp70\Rector\Coalesce\DowngradeNullCoalesceRector;
use Rector\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector;
use Rector\DowngradePhp73\Rector\FuncCall\DowngradeArrayKeyFirstLastRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(PhpVersion::PHP_70);
    $rectorConfig->rule(DowngradeArrayKeyFirstLastRector::class);
    $rectorConfig->rule(DowngradeScalarTypeDeclarationRector::class);
    $rectorConfig->rule(DowngradeNullCoalesceRector::class);
};
