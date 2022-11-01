<?php

declare (strict_types=1);
namespace RectorPrefix202211;

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector;
use Rector\DowngradePhp56\Rector\FuncCall\DowngradeArrayFilterUseConstantRector;
use Rector\DowngradePhp56\Rector\Pow\DowngradeExponentialAssignmentOperatorRector;
use Rector\DowngradePhp56\Rector\Pow\DowngradeExponentialOperatorRector;
use Rector\DowngradePhp56\Rector\Use_\DowngradeUseFunctionRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(PhpVersion::PHP_55);
    $rectorConfig->rule(DowngradeArgumentUnpackingRector::class);
    $rectorConfig->rule(DowngradeUseFunctionRector::class);
    $rectorConfig->rule(DowngradeExponentialAssignmentOperatorRector::class);
    $rectorConfig->rule(DowngradeExponentialOperatorRector::class);
    $rectorConfig->rule(DowngradeArrayFilterUseConstantRector::class);
};
