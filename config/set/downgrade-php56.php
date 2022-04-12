<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp56\Rector\CallLike\DowngradeArgumentUnpackingRector;
use Rector\DowngradePhp56\Rector\FuncCall\DowngradeArrayFilterUseConstantRector;
use Rector\DowngradePhp56\Rector\Pow\DowngradeExponentialAssignmentOperatorRector;
use Rector\DowngradePhp56\Rector\Pow\DowngradeExponentialOperatorRector;
use Rector\DowngradePhp56\Rector\Use_\DowngradeUseFunctionRector;

return static function (RectorConfig $rectorConfig): void {
    $parameters = $rectorConfig->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_55);

    $services = $rectorConfig->services();
    $services->set(DowngradeArgumentUnpackingRector::class);
    $services->set(DowngradeUseFunctionRector::class);
    $services->set(DowngradeExponentialAssignmentOperatorRector::class);
    $services->set(DowngradeExponentialOperatorRector::class);
    $services->set(DowngradeArrayFilterUseConstantRector::class);
};
