<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector;
use Rector\DowngradePhp72\Rector\ConstFetch\DowngradePhp72JsonConstRector;
use Rector\DowngradePhp72\Rector\FuncCall\DowngradeJsonDecodeNullAssociativeArgRector;
use Rector\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector;
use Rector\DowngradePhp72\Rector\FuncCall\DowngradeStreamIsattyRector;
use Rector\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(\Rector\Core\ValueObject\PhpVersion::PHP_71);
    $rectorConfig->rule(\Rector\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp72\Rector\FuncCall\DowngradeStreamIsattyRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp72\Rector\FuncCall\DowngradeJsonDecodeNullAssociativeArgRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp72\Rector\ConstFetch\DowngradePhp72JsonConstRector::class);
};
