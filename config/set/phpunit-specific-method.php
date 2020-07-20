<?php

declare(strict_types=1);

use Rector\PHPUnit\Rector\Foreach_\SimplifyForeachInstanceOfRector;
use Rector\PHPUnit\Rector\SpecificMethod\AssertCompareToSpecificMethodRector;
use Rector\PHPUnit\Rector\SpecificMethod\AssertComparisonToSpecificMethodRector;
use Rector\PHPUnit\Rector\SpecificMethod\AssertFalseStrposToContainsRector;
use Rector\PHPUnit\Rector\SpecificMethod\AssertInstanceOfComparisonRector;
use Rector\PHPUnit\Rector\SpecificMethod\AssertIssetToSpecificMethodRector;
use Rector\PHPUnit\Rector\SpecificMethod\AssertNotOperatorRector;
use Rector\PHPUnit\Rector\SpecificMethod\AssertPropertyExistsRector;
use Rector\PHPUnit\Rector\SpecificMethod\AssertRegExpRector;
use Rector\PHPUnit\Rector\SpecificMethod\AssertSameBoolNullToSpecificMethodRector;
use Rector\PHPUnit\Rector\SpecificMethod\AssertTrueFalseInternalTypeToSpecificMethodRector;
use Rector\PHPUnit\Rector\SpecificMethod\AssertTrueFalseToSpecificMethodRector;
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
