<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Nette\Rector\FuncCall\FilePutContentsToFileSystemWriteRector;
use RectorPrefix20220606\Rector\Nette\Rector\FuncCall\JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector;
use RectorPrefix20220606\Rector\Nette\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector;
use RectorPrefix20220606\Rector\Nette\Rector\FuncCall\PregMatchFunctionToNetteUtilsStringsRector;
use RectorPrefix20220606\Rector\Nette\Rector\FuncCall\SubstrStrlenFunctionToNetteUtilsStringsRector;
use RectorPrefix20220606\Rector\Nette\Rector\Identical\EndsWithFunctionToNetteUtilsStringsRector;
use RectorPrefix20220606\Rector\Nette\Rector\Identical\StartsWithFunctionToNetteUtilsStringsRector;
use RectorPrefix20220606\Rector\Nette\Rector\LNumber\ReplaceTimeNumberWithDateTimeConstantRector;
use RectorPrefix20220606\Rector\Nette\Rector\NotIdentical\StrposToStringsContainsRector;
use RectorPrefix20220606\Rector\Transform\Rector\FuncCall\FuncCallToStaticCallRector;
use RectorPrefix20220606\Rector\Transform\ValueObject\FuncCallToStaticCall;
# @see https://tomasvotruba.com/blog/2018/07/30/hidden-gems-of-php-packages-nette-utils
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(FuncCallToStaticCallRector::class, [new FuncCallToStaticCall('file_get_contents', 'Nette\\Utils\\FileSystem', 'read'), new FuncCallToStaticCall('unlink', 'Nette\\Utils\\FileSystem', 'delete'), new FuncCallToStaticCall('rmdir', 'Nette\\Utils\\FileSystem', 'delete')]);
    $rectorConfig->rule(StrposToStringsContainsRector::class);
    $rectorConfig->rule(SubstrStrlenFunctionToNetteUtilsStringsRector::class);
    $rectorConfig->rule(StartsWithFunctionToNetteUtilsStringsRector::class);
    $rectorConfig->rule(PregMatchFunctionToNetteUtilsStringsRector::class);
    $rectorConfig->rule(PregFunctionToNetteUtilsStringsRector::class);
    $rectorConfig->rule(EndsWithFunctionToNetteUtilsStringsRector::class);
    $rectorConfig->rule(JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector::class);
    $rectorConfig->rule(FilePutContentsToFileSystemWriteRector::class);
    $rectorConfig->rule(ReplaceTimeNumberWithDateTimeConstantRector::class);
};
