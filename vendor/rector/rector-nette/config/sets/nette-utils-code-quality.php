<?php

declare (strict_types=1);
namespace RectorPrefix20220501;

use Rector\Config\RectorConfig;
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
# @see https://tomasvotruba.com/blog/2018/07/30/hidden-gems-of-php-packages-nette-utils
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(\Rector\Transform\Rector\FuncCall\FuncCallToStaticCallRector::class, [new \Rector\Transform\ValueObject\FuncCallToStaticCall('file_get_contents', 'Nette\\Utils\\FileSystem', 'read'), new \Rector\Transform\ValueObject\FuncCallToStaticCall('unlink', 'Nette\\Utils\\FileSystem', 'delete'), new \Rector\Transform\ValueObject\FuncCallToStaticCall('rmdir', 'Nette\\Utils\\FileSystem', 'delete')]);
    $rectorConfig->rule(\Rector\Nette\Rector\NotIdentical\StrposToStringsContainsRector::class);
    $rectorConfig->rule(\Rector\Nette\Rector\FuncCall\SubstrStrlenFunctionToNetteUtilsStringsRector::class);
    $rectorConfig->rule(\Rector\Nette\Rector\Identical\StartsWithFunctionToNetteUtilsStringsRector::class);
    $rectorConfig->rule(\Rector\Nette\Rector\FuncCall\PregMatchFunctionToNetteUtilsStringsRector::class);
    $rectorConfig->rule(\Rector\Nette\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector::class);
    $rectorConfig->rule(\Rector\Nette\Rector\Identical\EndsWithFunctionToNetteUtilsStringsRector::class);
    $rectorConfig->rule(\Rector\Nette\Rector\FuncCall\JsonDecodeEncodeToNetteUtilsJsonDecodeEncodeRector::class);
    $rectorConfig->rule(\Rector\Nette\Rector\FuncCall\FilePutContentsToFileSystemWriteRector::class);
    $rectorConfig->rule(\Rector\Nette\Rector\LNumber\ReplaceTimeNumberWithDateTimeConstantRector::class);
};
