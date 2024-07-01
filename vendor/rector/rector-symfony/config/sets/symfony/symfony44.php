<?php

declare (strict_types=1);
namespace RectorPrefix202407;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Symfony\Symfony44\Rector\ClassMethod\ConsoleExecuteReturnIntRector;
use Rector\Symfony\Symfony44\Rector\MethodCall\AuthorizationCheckerIsGrantedExtractorRector;
# https://github.com/symfony/symfony/blob/4.4/UPGRADE-4.4.md
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        // https://github.com/symfony/symfony/pull/33775
        ConsoleExecuteReturnIntRector::class,
        // https://github.com/symfony/symfony/blob/4.4/UPGRADE-4.4.md#security
        AuthorizationCheckerIsGrantedExtractorRector::class,
    ]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Symfony\\Component\\Templating\\EngineInterface' => 'Twig\\Environment']);
    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, ['Symfony\\Component\\DependencyInjection\\Loader\\Configurator\\tagged' => 'Symfony\\Component\\DependencyInjection\\Loader\\Configurator\\tagged_iterator']);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        # https://github.com/symfony/http-kernel/blob/801b925e308518ddf821ba91952c41ae77c77507/Event/GetResponseForExceptionEvent.php#L55
        new MethodCallRename('Symfony\\Component\\HttpKernel\\Event\\GetResponseForExceptionEvent', 'getException', 'getThrowable'),
        # https://github.com/symfony/http-kernel/blob/801b925e308518ddf821ba91952c41ae77c77507/Event/GetResponseForExceptionEvent.php#L67
        new MethodCallRename('Symfony\\Component\\HttpKernel\\Event\\GetResponseForExceptionEvent', 'setException', 'setThrowable'),
    ]);
};
