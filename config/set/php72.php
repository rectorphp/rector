<?php

declare (strict_types=1);
namespace RectorPrefix20220209;

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
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Php72\Rector\While_\WhileEachToForeachRector::class);
    $services->set(\Rector\Php72\Rector\Assign\ListEachRector::class);
    $services->set(\Rector\Php72\Rector\Assign\ReplaceEachAssignmentWithKeyCurrentRector::class);
    $services->set(\Rector\Php72\Rector\Unset_\UnsetCastRector::class);
    $services->set(\Rector\Renaming\Rector\FuncCall\RenameFunctionRector::class)->configure([
        # and imagewbmp
        'jpeg2wbmp' => 'imagecreatefromjpeg',
        # or imagewbmp
        'png2wbmp' => 'imagecreatefrompng',
        #migration72.deprecated.gmp_random-function
        # http://php.net/manual/en/migration72.deprecated.php
        # or gmp_random_range
        'gmp_random' => 'gmp_random_bits',
        'read_exif_data' => 'exif_read_data',
    ]);
    $services->set(\Rector\Php72\Rector\FuncCall\GetClassOnNullRector::class);
    $services->set(\Rector\Php72\Rector\FuncCall\IsObjectOnIncompleteClassRector::class);
    $services->set(\Rector\Php72\Rector\FuncCall\ParseStrWithResultArgumentRector::class);
    $services->set(\Rector\Php72\Rector\FuncCall\StringsAssertNakedRector::class);
    $services->set(\Rector\Php72\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector::class);
    $services->set(\Rector\Php72\Rector\FuncCall\StringifyDefineRector::class);
};
