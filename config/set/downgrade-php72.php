<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Core\ValueObject\PhpVersion;
use RectorPrefix20220606\Rector\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector;
use RectorPrefix20220606\Rector\DowngradePhp72\Rector\ConstFetch\DowngradePhp72JsonConstRector;
use RectorPrefix20220606\Rector\DowngradePhp72\Rector\FuncCall\DowngradeJsonDecodeNullAssociativeArgRector;
use RectorPrefix20220606\Rector\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector;
use RectorPrefix20220606\Rector\DowngradePhp72\Rector\FuncCall\DowngradeStreamIsattyRector;
use RectorPrefix20220606\Rector\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(PhpVersion::PHP_71);
    $rectorConfig->rule(DowngradeObjectTypeDeclarationRector::class);
    $rectorConfig->rule(DowngradeParameterTypeWideningRector::class);
    $rectorConfig->rule(DowngradePregUnmatchedAsNullConstantRector::class);
    $rectorConfig->rule(DowngradeStreamIsattyRector::class);
    $rectorConfig->rule(DowngradeJsonDecodeNullAssociativeArgRector::class);
    $rectorConfig->rule(DowngradePhp72JsonConstRector::class);
};
