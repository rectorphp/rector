<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector;
use Rector\DowngradePhp72\Rector\ConstFetch\DowngradePhp72JsonConstRector;
use Rector\DowngradePhp72\Rector\FuncCall\DowngradeJsonDecodeNullAssociativeArgRector;
use Rector\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector;
use Rector\DowngradePhp72\Rector\FuncCall\DowngradeStreamIsattyRector;
use Rector\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector;

return static function (RectorConfig $rectorConfig): void {
    $parameters = $rectorConfig->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_71);

    $services = $rectorConfig->services();
    $services->set(DowngradeObjectTypeDeclarationRector::class);
    $services->set(DowngradeParameterTypeWideningRector::class);
    $services->set(DowngradePregUnmatchedAsNullConstantRector::class);
    $services->set(DowngradeStreamIsattyRector::class);
    $services->set(DowngradeJsonDecodeNullAssociativeArgRector::class);
    $services->set(DowngradePhp72JsonConstRector::class);
};
