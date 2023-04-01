<?php

declare (strict_types=1);
namespace RectorPrefix202304;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Symfony\Rector\ClassMethod\ConsoleExecuteReturnIntRector;
use Rector\Symfony\Rector\MethodCall\AuthorizationCheckerIsGrantedExtractorRector;
# https://github.com/symfony/symfony/blob/4.4/UPGRADE-4.4.md
return static function (RectorConfig $rectorConfig) : void {
    # https://github.com/symfony/symfony/pull/33775
    $rectorConfig->rule(ConsoleExecuteReturnIntRector::class);
    # https://github.com/symfony/symfony/blob/4.4/UPGRADE-4.4.md#security
    $rectorConfig->rule(AuthorizationCheckerIsGrantedExtractorRector::class);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        # https://github.com/symfony/http-kernel/blob/801b925e308518ddf821ba91952c41ae77c77507/Event/GetResponseForExceptionEvent.php#L55
        new MethodCallRename('Symfony\\Component\\HttpKernel\\Event\\GetResponseForExceptionEvent', 'getException', 'getThrowable'),
        # https://github.com/symfony/http-kernel/blob/801b925e308518ddf821ba91952c41ae77c77507/Event/GetResponseForExceptionEvent.php#L67
        new MethodCallRename('Symfony\\Component\\HttpKernel\\Event\\GetResponseForExceptionEvent', 'setException', 'setThrowable'),
    ]);
};
