<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
# https://github.com/symfony/symfony/blob/5.0/UPGRADE-5.0.md
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/symfony50-types.php');
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\Name\RenameClassRector::class, ['Symfony\\Component\\Debug\\Debug' => 'Symfony\\Component\\ErrorHandler\\Debug']);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class, [new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\Console\\Application', 'renderException', 'renderThrowable'), new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\Console\\Application', 'doRenderException', 'doRenderThrowable')]);
};
