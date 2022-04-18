<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Rector\Class_\TestListenerToHooksRector;
use Rector\PHPUnit\Rector\MethodCall\ExplicitPhpErrorApiRector;
use Rector\PHPUnit\Rector\MethodCall\SpecificAssertContainsWithoutIdentityRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $services = $rectorConfig->services();
    $services->set(\Rector\PHPUnit\Rector\Class_\TestListenerToHooksRector::class);
    $services->set(\Rector\PHPUnit\Rector\MethodCall\ExplicitPhpErrorApiRector::class);
    $services->set(\Rector\PHPUnit\Rector\MethodCall\SpecificAssertContainsWithoutIdentityRector::class);
    $services->set(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class)->configure([
        // see https://github.com/sebastianbergmann/phpunit/issues/3957
        new \Rector\Renaming\ValueObject\MethodCallRename('PHPUnit\\Framework\\TestCase', 'expectExceptionMessageRegExp', 'expectExceptionMessageMatches'),
    ]);
};
