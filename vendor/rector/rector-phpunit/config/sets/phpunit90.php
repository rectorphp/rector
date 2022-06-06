<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\PHPUnit\Rector\Class_\TestListenerToHooksRector;
use RectorPrefix20220606\Rector\PHPUnit\Rector\MethodCall\ExplicitPhpErrorApiRector;
use RectorPrefix20220606\Rector\PHPUnit\Rector\MethodCall\SpecificAssertContainsWithoutIdentityRector;
use RectorPrefix20220606\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use RectorPrefix20220606\Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(TestListenerToHooksRector::class);
    $rectorConfig->rule(ExplicitPhpErrorApiRector::class);
    $rectorConfig->rule(SpecificAssertContainsWithoutIdentityRector::class);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // see https://github.com/sebastianbergmann/phpunit/issues/3957
        new MethodCallRename('PHPUnit\\Framework\\TestCase', 'expectExceptionMessageRegExp', 'expectExceptionMessageMatches'),
    ]);
};
