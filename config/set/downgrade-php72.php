<?php

declare (strict_types=1);
namespace RectorPrefix20220412;

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector;
use Rector\DowngradePhp72\Rector\ConstFetch\DowngradePhp72JsonConstRector;
use Rector\DowngradePhp72\Rector\FuncCall\DowngradeJsonDecodeNullAssociativeArgRector;
use Rector\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector;
use Rector\DowngradePhp72\Rector\FuncCall\DowngradeStreamIsattyRector;
use Rector\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $parameters = $rectorConfig->parameters();
    $parameters->set(\Rector\Core\Configuration\Option::PHP_VERSION_FEATURES, \Rector\Core\ValueObject\PhpVersion::PHP_71);
    $services = $rectorConfig->services();
    $services->set(\Rector\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector::class);
    $services->set(\Rector\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector::class);
    $services->set(\Rector\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector::class);
    $services->set(\Rector\DowngradePhp72\Rector\FuncCall\DowngradeStreamIsattyRector::class);
    $services->set(\Rector\DowngradePhp72\Rector\FuncCall\DowngradeJsonDecodeNullAssociativeArgRector::class);
    $services->set(\Rector\DowngradePhp72\Rector\ConstFetch\DowngradePhp72JsonConstRector::class);
};
