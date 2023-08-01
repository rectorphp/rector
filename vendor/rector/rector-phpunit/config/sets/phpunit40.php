<?php

declare (strict_types=1);
namespace RectorPrefix202308;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename(
        'PHPUnit_Framework_MockObject_MockObject',
        # see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/137
        'staticExpects',
        'expects'
    )]);
};
