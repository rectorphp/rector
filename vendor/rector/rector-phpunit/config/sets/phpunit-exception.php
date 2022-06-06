<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\PHPUnit\Rector\ClassMethod\ExceptionAnnotationRector;
use RectorPrefix20220606\Rector\PHPUnit\Rector\MethodCall\DelegateExceptionArgumentsRector;
use RectorPrefix20220606\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use RectorPrefix20220606\Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    # handles 2nd and 3rd argument of setExpectedException
    $rectorConfig->rule(DelegateExceptionArgumentsRector::class);
    $rectorConfig->rule(ExceptionAnnotationRector::class);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('PHPUnit\\Framework\\TestClass', 'setExpectedException', 'expectedException'), new MethodCallRename('PHPUnit\\Framework\\TestClass', 'setExpectedExceptionRegExp', 'expectedException')]);
};
