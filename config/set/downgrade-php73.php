<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;

use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp73\Rector\FuncCall\DowngradeArrayKeyFirstLastRector;
use Rector\DowngradePhp73\Rector\FuncCall\DowngradeIsCountableRector;
use Rector\DowngradePhp73\Rector\FuncCall\DowngradeTrailingCommasInFunctionCallsRector;
use Rector\DowngradePhp73\Rector\FuncCall\SetCookieOptionsArrayToArgumentsRector;
use Rector\DowngradePhp73\Rector\List_\DowngradeListReferenceAssignmentRector;
use Rector\DowngradePhp73\Rector\String_\DowngradeFlexibleHeredocSyntaxRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_72);

    $services = $containerConfigurator->services();
    $services->set(DowngradeFlexibleHeredocSyntaxRector::class);
    $services->set(DowngradeListReferenceAssignmentRector::class);
    $services->set(DowngradeTrailingCommasInFunctionCallsRector::class);
    $services->set(DowngradeArrayKeyFirstLastRector::class);
    $services->set(SetCookieOptionsArrayToArgumentsRector::class);
    $services->set(DowngradeIsCountableRector::class);
};
