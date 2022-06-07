<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\MethodCallRenameWithArrayKey;
# https://github.com/symfony/symfony/blob/master/UPGRADE-4.1.md
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        # https://github.com/symfony/symfony/commit/463f986c28a497571967e37c1314e9911f1ef6ba
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Console\\Helper\\TableStyle', 'setHorizontalBorderChar', 'setHorizontalBorderChars'),
        # https://github.com/symfony/symfony/commit/463f986c28a497571967e37c1314e9911f1ef6ba
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Console\\Helper\\TableStyle', 'setVerticalBorderChar', 'setVerticalBorderChars'),
        # https://github.com/symfony/symfony/commit/463f986c28a497571967e37c1314e9911f1ef6ba
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Console\\Helper\\TableStyle', 'setCrossingChar', 'setDefaultCrossingChar'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\HttpFoundation\\File\\UploadedFile', 'getClientSize', 'getSize'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Workflow\\DefinitionBuilder', 'reset', 'clear'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Workflow\\DefinitionBuilder', 'add', 'addWorkflow'),
        # https://github.com/symfony/symfony/commit/463f986c28a497571967e37c1314e9911f1ef6ba
        new MethodCallRenameWithArrayKey(
            'RectorPrefix20220607\\Symfony\\Component\\Console\\Helper\\TableStyle',
            'getVerticalBorderChar',
            # special case to "getVerticalBorderChar" → "getBorderChars()[3]"
            'getBorderChars',
            3
        ),
        # https://github.com/symfony/symfony/commit/463f986c28a497571967e37c1314e9911f1ef6ba
        new MethodCallRenameWithArrayKey('RectorPrefix20220607\\Symfony\\Component\\Console\\Helper\\TableStyle', 'getHorizontalBorderChar', 'getBorderChars', 2),
    ]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        # https://github.com/symfony/symfony/commit/07dd09db59e2f2a86a291d00d978169d9059e307
        'RectorPrefix20220607\\Symfony\\Bundle\\FrameworkBundle\\DataCollector\\RequestDataCollector' => 'RectorPrefix20220607\\Symfony\\Component\\HttpKernel\\DataCollector\\RequestDataCollector',
        'RectorPrefix20220607\\Symfony\\Component\\Workflow\\SupportStrategy\\SupportStrategyInterface' => 'RectorPrefix20220607\\Symfony\\Component\\Workflow\\SupportStrategy\\WorkflowSupportStrategyInterface',
        'RectorPrefix20220607\\Symfony\\Component\\Workflow\\SupportStrategy\\ClassInstanceSupportStrategy' => 'RectorPrefix20220607\\Symfony\\Component\\Workflow\\SupportStrategy\\InstanceOfSupportStrategy',
    ]);
};
