<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector;
use Rector\DowngradePhp56\Rector\FuncCall\DowngradeArrayFilterUseConstantRector;
use Rector\DowngradePhp56\Rector\Pow\DowngradeExponentialAssignmentOperatorRector;
use Rector\DowngradePhp56\Rector\Pow\DowngradeExponentialOperatorRector;
use Rector\DowngradePhp56\Rector\Use_\DowngradeUseFunctionRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(\Rector\Core\ValueObject\PhpVersion::PHP_55);
    $rectorConfig->rule(\Rector\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp56\Rector\Use_\DowngradeUseFunctionRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp56\Rector\Pow\DowngradeExponentialAssignmentOperatorRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp56\Rector\Pow\DowngradeExponentialOperatorRector::class);
    $rectorConfig->rule(\Rector\DowngradePhp56\Rector\FuncCall\DowngradeArrayFilterUseConstantRector::class);
};
