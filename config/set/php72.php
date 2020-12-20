<?php

declare(strict_types=1);

use Rector\Php72\Rector\Assign\ListEachRector;
use Rector\Php72\Rector\Assign\ReplaceEachAssignmentWithKeyCurrentRector;
use Rector\Php72\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector;
use Rector\Php72\Rector\FuncCall\GetClassOnNullRector;
use Rector\Php72\Rector\FuncCall\IsObjectOnIncompleteClassRector;
use Rector\Php72\Rector\FuncCall\ParseStrWithResultArgumentRector;
use Rector\Php72\Rector\FuncCall\StringifyDefineRector;
use Rector\Php72\Rector\FuncCall\StringsAssertNakedRector;
use Rector\Php72\Rector\Unset_\UnsetCastRector;
use Rector\Php72\Rector\While_\WhileEachToForeachRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(WhileEachToForeachRector::class);

    $services->set(ListEachRector::class);

    $services->set(ReplaceEachAssignmentWithKeyCurrentRector::class);

    $services->set(UnsetCastRector::class);

    $services->set(RenameFunctionRector::class)
        ->call('configure', [[
            RenameFunctionRector::OLD_FUNCTION_TO_NEW_FUNCTION => [
                # and imagewbmp
                'jpeg2wbmp' => 'imagecreatefromjpeg',
                # or imagewbmp
                'png2wbmp' => 'imagecreatefrompng',
                #migration72.deprecated.gmp_random-function
                # http://php.net/manual/en/migration72.deprecated.php
                # or gmp_random_range
                'gmp_random' => 'gmp_random_bits',
                'read_exif_data' => 'exif_read_data',
            ],
        ]]);

    $services->set(GetClassOnNullRector::class);

    $services->set(IsObjectOnIncompleteClassRector::class);

    $services->set(ParseStrWithResultArgumentRector::class);

    $services->set(StringsAssertNakedRector::class);

    $services->set(CreateFunctionToAnonymousFunctionRector::class);

    $services->set(StringifyDefineRector::class);
};
