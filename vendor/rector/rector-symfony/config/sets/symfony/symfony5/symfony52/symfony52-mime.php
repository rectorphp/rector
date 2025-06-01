<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
// https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#form
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#mime
        new MethodCallRename('Symfony\\Component\\Mime\\Address', 'fromString', 'create'),
    ]);
};
