<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // @see https://github.com/symfony/symfony/pull/42582
        new MethodCallRename('Symfony\\Bundle\\SecurityBundle\\Security\\FirewallConfig', 'getListeners', 'getAuthenticators'),
        // @see https://github.com/symfony/symfony/pull/41754
        new MethodCallRename('Symfony\\Bundle\\SecurityBundle\\DependencyInjection\\SecurityExtension', 'addSecurityListenerFactory', 'addAuthenticatorFactory'),
    ]);
};
