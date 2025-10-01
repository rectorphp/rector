<?php

declare (strict_types=1);
namespace RectorPrefix202510;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig): void {
    // @see https://github.com/symfony/symfony/blob/7.4/UPGRADE-7.4.md#console
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Symfony\Component\Console\Application', 'add', 'addCommand')]);
};
