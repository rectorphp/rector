<?php

declare (strict_types=1);
namespace RectorPrefix202304;

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector;
use Rector\DowngradePhp72\Rector\ConstFetch\DowngradePhp72JsonConstRector;
use Rector\DowngradePhp72\Rector\FuncCall\DowngradeJsonDecodeNullAssociativeArgRector;
use Rector\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector;
use Rector\DowngradePhp72\Rector\FuncCall\DowngradeStreamIsattyRector;
use Rector\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(PhpVersion::PHP_71);
    $rectorConfig->rule(DowngradeObjectTypeDeclarationRector::class);
    $rectorConfig->rule(DowngradeParameterTypeWideningRector::class);
    $rectorConfig->rule(DowngradePregUnmatchedAsNullConstantRector::class);
    $rectorConfig->rule(DowngradeStreamIsattyRector::class);
    $rectorConfig->rule(DowngradeJsonDecodeNullAssociativeArgRector::class);
    $rectorConfig->rule(DowngradePhp72JsonConstRector::class);
};
