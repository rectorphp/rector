<?php

declare(strict_types=1);

use Rector\PHPUnit\Rector\Foreach_\SimplifyForeachInstanceOfRector;
use Rector\PHPUnit\Rector\MethodCall\AssertCompareToSpecificMethodRector;
use Rector\PHPUnit\Rector\MethodCall\AssertComparisonToSpecificMethodRector;
use Rector\PHPUnit\Rector\MethodCall\AssertFalseStrposToContainsRector;
use Rector\PHPUnit\Rector\MethodCall\AssertInstanceOfComparisonRector;
use Rector\PHPUnit\Rector\MethodCall\AssertIssetToSpecificMethodRector;
use Rector\PHPUnit\Rector\MethodCall\AssertNotOperatorRector;
use Rector\PHPUnit\Rector\MethodCall\AssertPropertyExistsRector;
use Rector\PHPUnit\Rector\MethodCall\AssertRegExpRector;
use Rector\PHPUnit\Rector\MethodCall\AssertSameBoolNullToSpecificMethodRector;
use Rector\PHPUnit\Rector\MethodCall\AssertTrueFalseInternalTypeToSpecificMethodRector;
use Rector\PHPUnit\Rector\MethodCall\AssertTrueFalseToSpecificMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AssertNotOperatorRector::class);

    $services->set(AssertComparisonToSpecificMethodRector::class);

    $services->set(AssertTrueFalseToSpecificMethodRector::class);

    $services->set(AssertSameBoolNullToSpecificMethodRector::class);

    $services->set(AssertFalseStrposToContainsRector::class);

    $services->set(AssertTrueFalseInternalTypeToSpecificMethodRector::class);

    $services->set(AssertCompareToSpecificMethodRector::class);

    $services->set(AssertIssetToSpecificMethodRector::class);

    $services->set(AssertInstanceOfComparisonRector::class);

    $services->set(AssertPropertyExistsRector::class);

    $services->set(AssertRegExpRector::class);

    $services->set(SimplifyForeachInstanceOfRector::class);
};
