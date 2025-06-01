<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    // @see https://github.com/symfony/symfony/blob/7.1/UPGRADE-7.1.md#dependencyinjection
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // typo fix
        new MethodCallRename('Symfony\\Component\\Serializer\\Context\\Normalizer\\AbstractNormalizerContextBuilder', 'withDefaultContructorArguments', 'withDefaultConstructorArguments'),
    ]);
};
