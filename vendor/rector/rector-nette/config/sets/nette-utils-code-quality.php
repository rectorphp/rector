<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

use Rector\Nette\Rector\FuncCall\FilePutContentsToFileSystemWriteRector;
use Rector\Nette\Rector\FuncCall\JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector;
use Rector\Nette\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector;
use Rector\Nette\Rector\FuncCall\PregMatchFunctionToNetteUtilsStringsRector;
use Rector\Nette\Rector\FuncCall\SubstrStrlenFunctionToNetteUtilsStringsRector;
use Rector\Nette\Rector\Identical\EndsWithFunctionToNetteUtilsStringsRector;
use Rector\Nette\Rector\Identical\StartsWithFunctionToNetteUtilsStringsRector;
use Rector\Nette\Rector\LNumber\ReplaceTimeNumberWithDateTimeConstantRector;
use Rector\Nette\Rector\NotIdentical\StrposToStringsContainsRector;
use Rector\Transform\Rector\FuncCall\FuncCallToStaticCallRector;
use Rector\Transform\ValueObject\FuncCallToStaticCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;
# @see https://tomasvotruba.com/blog/2018/07/30/hidden-gems-of-php-packages-nette-utils
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Transform\Rector\FuncCall\FuncCallToStaticCallRector::class)->call('configure', [[\Rector\Transform\Rector\FuncCall\FuncCallToStaticCallRector::FUNC_CALLS_TO_STATIC_CALLS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\Transform\ValueObject\FuncCallToStaticCall('file_get_contents', 'Nette\\Utils\\FileSystem', 'read'), new \Rector\Transform\ValueObject\FuncCallToStaticCall('unlink', 'Nette\\Utils\\FileSystem', 'delete'), new \Rector\Transform\ValueObject\FuncCallToStaticCall('rmdir', 'Nette\\Utils\\FileSystem', 'delete')])]]);
    $services->set(\Rector\Nette\Rector\NotIdentical\StrposToStringsContainsRector::class);
    $services->set(\Rector\Nette\Rector\FuncCall\SubstrStrlenFunctionToNetteUtilsStringsRector::class);
    $services->set(\Rector\Nette\Rector\Identical\StartsWithFunctionToNetteUtilsStringsRector::class);
    $services->set(\Rector\Nette\Rector\FuncCall\PregMatchFunctionToNetteUtilsStringsRector::class);
    $services->set(\Rector\Nette\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector::class);
    $services->set(\Rector\Nette\Rector\Identical\EndsWithFunctionToNetteUtilsStringsRector::class);
    $services->set(\Rector\Nette\Rector\FuncCall\JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector::class);
    $services->set(\Rector\Nette\Rector\FuncCall\FilePutContentsToFileSystemWriteRector::class);
    $services->set(\Rector\Nette\Rector\LNumber\ReplaceTimeNumberWithDateTimeConstantRector::class);
};
