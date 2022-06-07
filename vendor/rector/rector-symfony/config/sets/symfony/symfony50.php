<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
# https://github.com/symfony/symfony/blob/5.0/UPGRADE-5.0.md
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/symfony50-types.php');
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['RectorPrefix20220607\\Symfony\\Component\\Debug\\Debug' => 'RectorPrefix20220607\\Symfony\\Component\\ErrorHandler\\Debug']);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Console\\Application', 'renderException', 'renderThrowable'), new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Console\\Application', 'doRenderException', 'doRenderThrowable')]);
};
