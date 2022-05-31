<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector;
use Rector\Arguments\ValueObject\ReplaceArgumentDefaultValue;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;
use Rector\Nette\Rector\Class_\MoveFinalGetUserToCheckRequirementsClassMethodRector;
use Rector\Nette\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector;
use Rector\Nette\Rector\MethodCall\AddNextrasDatePickerToDateControlRector;
use Rector\Nette\Rector\MethodCall\ConvertAddUploadWithThirdArgumentTrueToAddMultiUploadRector;
use Rector\Nette\Rector\MethodCall\MagicHtmlCallToAppendAttributeRector;
use Rector\Nette\Rector\MethodCall\MergeDefaultsInGetConfigCompilerExtensionRector;
use Rector\Nette\Rector\MethodCall\RequestGetCookieDefaultArgumentToCoalesceRector;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameClassConstFetch;
use Rector\Transform\Rector\StaticCall\StaticCallToMethodCallRector;
use Rector\Transform\ValueObject\StaticCallToMethodCall;
use Rector\TypeDeclaration\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->sets([__DIR__ . '/nette-30/nette-30-composer.php', __DIR__ . '/nette-30/nette-30-dependency-injection.php', __DIR__ . '/nette-30/nette-30-return-types.php', __DIR__ . '/nette-30/nette-30-param-types.php']);
    $rectorConfig->rule(\Rector\Nette\Rector\MethodCall\AddNextrasDatePickerToDateControlRector::class);
    $rectorConfig->rule(\Rector\Nette\Rector\MethodCall\MergeDefaultsInGetConfigCompilerExtensionRector::class);
    // Control class has remove __construct(), e.g. https://github.com/Pixidos/GPWebPay/pull/16/files#diff-fdc8251950f85c5467c63c249df05786
    $rectorConfig->rule(\Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector::class);
    // https://github.com/nette/utils/commit/d0041ba59f5d8bf1f5b3795fd76d43fb13ea2e15
    $rectorConfig->rule(\Rector\TypeDeclaration\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\Transform\Rector\StaticCall\StaticCallToMethodCallRector::class, [new \Rector\Transform\ValueObject\StaticCallToMethodCall('Nette\\Security\\Passwords', 'hash', 'Nette\\Security\\Passwords', 'hash'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Nette\\Security\\Passwords', 'verify', 'Nette\\Security\\Passwords', 'verify'), new \Rector\Transform\ValueObject\StaticCallToMethodCall('Nette\\Security\\Passwords', 'needsRehash', 'Nette\\Security\\Passwords', 'needsRehash')]);
    // https://github.com/contributte/event-dispatcher-extra/tree/v0.4.3 and higher
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector::class, [new \Rector\Renaming\ValueObject\RenameClassConstFetch('Contributte\\Events\\Extra\\Event\\Security\\LoggedInEvent', 'NAME', 'class'), new \Rector\Renaming\ValueObject\RenameClassConstFetch('Contributte\\Events\\Extra\\Event\\Security\\LoggedOutEvent', 'NAME', 'class'), new \Rector\Renaming\ValueObject\RenameClassConstFetch('Contributte\\Events\\Extra\\Event\\Application\\ShutdownEvent', 'NAME', 'class')]);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\Name\RenameClassRector::class, [
        # nextras/forms was split into 2 packages
        'Nextras\\FormComponents\\Controls\\DatePicker' => 'Nextras\\FormComponents\\Controls\\DateControl',
        # @see https://github.com/nette/di/commit/a0d361192f8ac35f1d9f82aab7eb351e4be395ea
        'Nette\\DI\\ServiceDefinition' => 'Nette\\DI\\Definitions\\ServiceDefinition',
        'Nette\\DI\\Statement' => 'Nette\\DI\\Definitions\\Statement',
        'WebChemistry\\Forms\\Controls\\Multiplier' => 'Contributte\\FormMultiplier\\Multiplier',
    ]);
    $rectorConfig->ruleWithConfiguration(\Rector\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector::class, [
        // json 2nd argument is now `int` typed
        new \Rector\Arguments\ValueObject\ReplaceArgumentDefaultValue('Nette\\Utils\\Json', 'decode', 1, \true, 'Nette\\Utils\\Json::FORCE_ARRAY'),
        // @see https://github.com/nette/forms/commit/574b97f9d5e7a902a224e57d7d584e7afc9fefec
        new \Rector\Arguments\ValueObject\ReplaceArgumentDefaultValue('Nette\\Forms\\Form', 'decode', 0, \true, 'array'),
    ]);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class, [
        // see https://github.com/nette/forms/commit/b99385aa9d24d729a18f6397a414ea88eab6895a
        new \Rector\Renaming\ValueObject\MethodCallRename('Nette\\Forms\\Controls\\BaseControl', 'setType', 'setHtmlType'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Nette\\Forms\\Controls\\BaseControl', 'setAttribute', 'setHtmlAttribute'),
        new \Rector\Renaming\ValueObject\MethodCallRename(
            'Nette\\DI\\Definitions\\ServiceDefinition',
            # see https://github.com/nette/di/commit/1705a5db431423fc610a6f339f88dead1b5dc4fb
            'setClass',
            'setType'
        ),
        new \Rector\Renaming\ValueObject\MethodCallRename('Nette\\DI\\Definitions\\ServiceDefinition', 'getClass', 'getType'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Nette\\DI\\Definitions\\Definition', 'isAutowired', 'getAutowired'),
    ]);
    $rectorConfig->rule(\Rector\Nette\Rector\MethodCall\MagicHtmlCallToAppendAttributeRector::class);
    $rectorConfig->rule(\Rector\Nette\Rector\MethodCall\RequestGetCookieDefaultArgumentToCoalesceRector::class);
    $rectorConfig->rule(\Rector\Nette\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector::class);
    $rectorConfig->rule(\Rector\Nette\Rector\Class_\MoveFinalGetUserToCheckRequirementsClassMethodRector::class);
    $rectorConfig->rule(\Rector\Nette\Rector\MethodCall\ConvertAddUploadWithThirdArgumentTrueToAddMultiUploadRector::class);
};
