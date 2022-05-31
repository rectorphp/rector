<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Rector\Class_\AddProphecyTraitRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\PHPUnit\Rector\Class_\AddProphecyTraitRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class, [
        // https://github.com/sebastianbergmann/phpunit/issues/4087
        new \Rector\Renaming\ValueObject\MethodCallRename('PHPUnit\\Framework\\Assert', 'assertRegExp', 'assertMatchesRegularExpression'),
        // https://github.com/sebastianbergmann/phpunit/issues/4090
        new \Rector\Renaming\ValueObject\MethodCallRename('PHPUnit\\Framework\\Assert', 'assertNotRegExp', 'assertDoesNotMatchRegularExpression'),
        // https://github.com/sebastianbergmann/phpunit/issues/4078
        new \Rector\Renaming\ValueObject\MethodCallRename('PHPUnit\\Framework\\Assert', 'assertFileNotExists', 'assertFileDoesNotExist'),
        // https://github.com/sebastianbergmann/phpunit/issues/4081
        new \Rector\Renaming\ValueObject\MethodCallRename('PHPUnit\\Framework\\Assert', 'assertFileNotIsReadable', 'assertFileIsNotReadable'),
        // https://github.com/sebastianbergmann/phpunit/issues/4072
        new \Rector\Renaming\ValueObject\MethodCallRename('PHPUnit\\Framework\\Assert', 'assertDirectoryNotIsReadable', 'assertDirectoryIsNotReadable'),
        // https://github.com/sebastianbergmann/phpunit/issues/4075
        new \Rector\Renaming\ValueObject\MethodCallRename('PHPUnit\\Framework\\Assert', 'assertDirectoryNotIsWritable', 'assertDirectoryIsNotWritable'),
        // https://github.com/sebastianbergmann/phpunit/issues/4069
        new \Rector\Renaming\ValueObject\MethodCallRename('PHPUnit\\Framework\\Assert', 'assertDirectoryNotExists', 'assertDirectoryDoesNotExist'),
        // https://github.com/sebastianbergmann/phpunit/issues/4066
        new \Rector\Renaming\ValueObject\MethodCallRename('PHPUnit\\Framework\\Assert', 'assertNotIsWritable', 'assertIsNotWritable'),
        // https://github.com/sebastianbergmann/phpunit/issues/4063
        new \Rector\Renaming\ValueObject\MethodCallRename('PHPUnit\\Framework\\Assert', 'assertNotIsReadable', 'assertIsNotReadable'),
    ]);
};
