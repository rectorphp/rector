<?php

declare (strict_types=1);
namespace RectorPrefix202304;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Rector\Class_\TestListenerToHooksRector;
use Rector\PHPUnit\Rector\MethodCall\ExplicitPhpErrorApiRector;
use Rector\PHPUnit\Rector\MethodCall\SpecificAssertContainsWithoutIdentityRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(TestListenerToHooksRector::class);
    $rectorConfig->rule(ExplicitPhpErrorApiRector::class);
    $rectorConfig->rule(SpecificAssertContainsWithoutIdentityRector::class);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // see https://github.com/sebastianbergmann/phpunit/issues/3957
        new MethodCallRename('PHPUnit\\Framework\\TestCase', 'expectExceptionMessageRegExp', 'expectExceptionMessageMatches'),
    ]);
};
