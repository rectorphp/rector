<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony52\Rector\MethodCall\DefinitionAliasSetPrivateToSetPublicRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#dependencyinjection
        DefinitionAliasSetPrivateToSetPublicRector::class,
    ]);
};
