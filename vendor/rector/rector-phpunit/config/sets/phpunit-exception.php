<?php

declare (strict_types=1);
namespace RectorPrefix202212;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Rector\ClassMethod\ExceptionAnnotationRector;
use Rector\PHPUnit\Rector\MethodCall\DelegateExceptionArgumentsRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    # handles 2nd and 3rd argument of setExpectedException
    $rectorConfig->rule(DelegateExceptionArgumentsRector::class);
    $rectorConfig->rule(ExceptionAnnotationRector::class);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('PHPUnit\\Framework\\TestClass', 'setExpectedException', 'expectedException'), new MethodCallRename('PHPUnit\\Framework\\TestClass', 'setExpectedExceptionRegExp', 'expectedException')]);
};
