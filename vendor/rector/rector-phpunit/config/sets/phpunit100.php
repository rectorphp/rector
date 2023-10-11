<?php

declare (strict_types=1);
namespace RectorPrefix202310;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\PHPUnit100\Rector\Class_\AddProphecyTraitRector;
use Rector\PHPUnit\PHPUnit100\Rector\Class_\StaticDataProviderClassMethodRector;
use Rector\PHPUnit\PHPUnit100\Rector\MethodCall\PropertyExistsWithoutAssertRector;
use Rector\PHPUnit\Rector\StmtsAwareInterface\WithConsecutiveRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->sets([PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES]);
    $rectorConfig->rules([StaticDataProviderClassMethodRector::class, PropertyExistsWithoutAssertRector::class, AddProphecyTraitRector::class, WithConsecutiveRector::class]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // https://github.com/sebastianbergmann/phpunit/issues/4087
        new MethodCallRename('PHPUnit\\Framework\\Assert', 'assertRegExp', 'assertMatchesRegularExpression'),
        new MethodCallRename('PHPUnit\\Framework\\MockObject\\Rule\\InvocationOrder', 'getInvocationCount', 'numberOfInvocations'),
        // https://github.com/sebastianbergmann/phpunit/issues/4090
        new MethodCallRename('PHPUnit\\Framework\\Assert', 'assertNotRegExp', 'assertDoesNotMatchRegularExpression'),
        // https://github.com/sebastianbergmann/phpunit/issues/4078
        new MethodCallRename('PHPUnit\\Framework\\Assert', 'assertFileNotExists', 'assertFileDoesNotExist'),
        // https://github.com/sebastianbergmann/phpunit/issues/4081
        new MethodCallRename('PHPUnit\\Framework\\Assert', 'assertFileNotIsReadable', 'assertFileIsNotReadable'),
        // https://github.com/sebastianbergmann/phpunit/issues/4072
        new MethodCallRename('PHPUnit\\Framework\\Assert', 'assertDirectoryNotIsReadable', 'assertDirectoryIsNotReadable'),
        // https://github.com/sebastianbergmann/phpunit/issues/4075
        new MethodCallRename('PHPUnit\\Framework\\Assert', 'assertDirectoryNotIsWritable', 'assertDirectoryIsNotWritable'),
        // https://github.com/sebastianbergmann/phpunit/issues/4069
        new MethodCallRename('PHPUnit\\Framework\\Assert', 'assertDirectoryNotExists', 'assertDirectoryDoesNotExist'),
        // https://github.com/sebastianbergmann/phpunit/issues/4066
        new MethodCallRename('PHPUnit\\Framework\\Assert', 'assertNotIsWritable', 'assertIsNotWritable'),
        // https://github.com/sebastianbergmann/phpunit/issues/4063
        new MethodCallRename('PHPUnit\\Framework\\Assert', 'assertNotIsReadable', 'assertIsNotReadable'),
    ]);
};
