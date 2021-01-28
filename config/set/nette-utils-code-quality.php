<?php

declare(strict_types=1);

use Rector\Nette\Rector\FuncCall\FilePutContentsToFileSystemWriteRector;
use Rector\Nette\Rector\FuncCall\JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector;
use Rector\Nette\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector;
use Rector\Nette\Rector\FuncCall\PregMatchFunctionToNetteUtilsStringsRector;
use Rector\Nette\Rector\FuncCall\SubstrStrlenFunctionToNetteUtilsStringsRector;
use Rector\Nette\Rector\Identical\EndsWithFunctionToNetteUtilsStringsRector;
use Rector\Nette\Rector\Identical\StartsWithFunctionToNetteUtilsStringsRector;
use Rector\Nette\Rector\NotIdentical\StrposToStringsContainsRector;
use Rector\NetteUtilsCodeQuality\Rector\LNumber\ReplaceTimeNumberWithDateTimeConstantRector;
use Rector\Transform\Rector\FuncCall\FuncCallToStaticCallRector;
use Rector\Transform\ValueObject\FuncCallToStaticCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

# @see https://www.tomasvotruba.cz/blog/2018/07/30/hidden-gems-of-php-packages-nette-utils
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(FuncCallToStaticCallRector::class)
        ->call('configure', [[
            FuncCallToStaticCallRector::FUNC_CALLS_TO_STATIC_CALLS => ValueObjectInliner::inline([
                new FuncCallToStaticCall('file_get_contents', 'Nette\Utils\FileSystem', 'read'),
                new FuncCallToStaticCall('unlink', 'Nette\Utils\FileSystem', 'delete'),
                new FuncCallToStaticCall('rmdir', 'Nette\Utils\FileSystem', 'delete'),
            ]),
        ]]);

    $services->set(StrposToStringsContainsRector::class);
    $services->set(SubstrStrlenFunctionToNetteUtilsStringsRector::class);
    $services->set(StartsWithFunctionToNetteUtilsStringsRector::class);
    $services->set(PregMatchFunctionToNetteUtilsStringsRector::class);
    $services->set(PregFunctionToNetteUtilsStringsRector::class);
    $services->set(EndsWithFunctionToNetteUtilsStringsRector::class);
    $services->set(JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector::class);
    $services->set(FilePutContentsToFileSystemWriteRector::class);
    $services->set(ReplaceTimeNumberWithDateTimeConstantRector::class);
};
