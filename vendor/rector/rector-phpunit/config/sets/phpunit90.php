<?php

declare (strict_types=1);
namespace RectorPrefix202411;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\PHPUnit100\Rector\StmtsAwareInterface\WithConsecutiveRector;
use Rector\PHPUnit\PHPUnit90\Rector\Class_\TestListenerToHooksRector;
use Rector\PHPUnit\PHPUnit90\Rector\MethodCall\ExplicitPhpErrorApiRector;
use Rector\PHPUnit\PHPUnit90\Rector\MethodCall\SpecificAssertContainsWithoutIdentityRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([TestListenerToHooksRector::class, ExplicitPhpErrorApiRector::class, SpecificAssertContainsWithoutIdentityRector::class, WithConsecutiveRector::class]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // see https://github.com/sebastianbergmann/phpunit/issues/3957
        new MethodCallRename('PHPUnit\\Framework\\TestCase', 'expectExceptionMessageRegExp', 'expectExceptionMessageMatches'),
    ]);
};
