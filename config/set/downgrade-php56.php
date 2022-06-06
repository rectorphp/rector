<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Core\ValueObject\PhpVersion;
use RectorPrefix20220606\Rector\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector;
use RectorPrefix20220606\Rector\DowngradePhp56\Rector\FuncCall\DowngradeArrayFilterUseConstantRector;
use RectorPrefix20220606\Rector\DowngradePhp56\Rector\Pow\DowngradeExponentialAssignmentOperatorRector;
use RectorPrefix20220606\Rector\DowngradePhp56\Rector\Pow\DowngradeExponentialOperatorRector;
use RectorPrefix20220606\Rector\DowngradePhp56\Rector\Use_\DowngradeUseFunctionRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(PhpVersion::PHP_55);
    $rectorConfig->rule(DowngradeArgumentUnpackingRector::class);
    $rectorConfig->rule(DowngradeUseFunctionRector::class);
    $rectorConfig->rule(DowngradeExponentialAssignmentOperatorRector::class);
    $rectorConfig->rule(DowngradeExponentialOperatorRector::class);
    $rectorConfig->rule(DowngradeArrayFilterUseConstantRector::class);
};
