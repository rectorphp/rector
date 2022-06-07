<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Rector\Class_\AddProphecyTraitRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(AddProphecyTraitRector::class);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // https://github.com/sebastianbergmann/phpunit/issues/4087
        new MethodCallRename('RectorPrefix20220607\\PHPUnit\\Framework\\Assert', 'assertRegExp', 'assertMatchesRegularExpression'),
        // https://github.com/sebastianbergmann/phpunit/issues/4090
        new MethodCallRename('RectorPrefix20220607\\PHPUnit\\Framework\\Assert', 'assertNotRegExp', 'assertDoesNotMatchRegularExpression'),
        // https://github.com/sebastianbergmann/phpunit/issues/4078
        new MethodCallRename('RectorPrefix20220607\\PHPUnit\\Framework\\Assert', 'assertFileNotExists', 'assertFileDoesNotExist'),
        // https://github.com/sebastianbergmann/phpunit/issues/4081
        new MethodCallRename('RectorPrefix20220607\\PHPUnit\\Framework\\Assert', 'assertFileNotIsReadable', 'assertFileIsNotReadable'),
        // https://github.com/sebastianbergmann/phpunit/issues/4072
        new MethodCallRename('RectorPrefix20220607\\PHPUnit\\Framework\\Assert', 'assertDirectoryNotIsReadable', 'assertDirectoryIsNotReadable'),
        // https://github.com/sebastianbergmann/phpunit/issues/4075
        new MethodCallRename('RectorPrefix20220607\\PHPUnit\\Framework\\Assert', 'assertDirectoryNotIsWritable', 'assertDirectoryIsNotWritable'),
        // https://github.com/sebastianbergmann/phpunit/issues/4069
        new MethodCallRename('RectorPrefix20220607\\PHPUnit\\Framework\\Assert', 'assertDirectoryNotExists', 'assertDirectoryDoesNotExist'),
        // https://github.com/sebastianbergmann/phpunit/issues/4066
        new MethodCallRename('RectorPrefix20220607\\PHPUnit\\Framework\\Assert', 'assertNotIsWritable', 'assertIsNotWritable'),
        // https://github.com/sebastianbergmann/phpunit/issues/4063
        new MethodCallRename('RectorPrefix20220607\\PHPUnit\\Framework\\Assert', 'assertNotIsReadable', 'assertIsNotReadable'),
    ]);
};
