<?php

declare (strict_types=1);
namespace RectorPrefix202208;

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
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->sets([__DIR__ . '/nette-30/nette-30-composer.php', __DIR__ . '/nette-30/nette-30-dependency-injection.php', __DIR__ . '/nette-30/nette-30-return-types.php', __DIR__ . '/nette-30/nette-30-param-types.php']);
    $rectorConfig->rule(AddNextrasDatePickerToDateControlRector::class);
    $rectorConfig->rule(MergeDefaultsInGetConfigCompilerExtensionRector::class);
    // Control class has remove __construct(), e.g. https://github.com/Pixidos/GPWebPay/pull/16/files#diff-fdc8251950f85c5467c63c249df05786
    $rectorConfig->rule(RemoveParentCallWithoutParentRector::class);
    $rectorConfig->ruleWithConfiguration(StaticCallToMethodCallRector::class, [new StaticCallToMethodCall('Nette\\Security\\Passwords', 'hash', 'Nette\\Security\\Passwords', 'hash'), new StaticCallToMethodCall('Nette\\Security\\Passwords', 'verify', 'Nette\\Security\\Passwords', 'verify'), new StaticCallToMethodCall('Nette\\Security\\Passwords', 'needsRehash', 'Nette\\Security\\Passwords', 'needsRehash')]);
    // https://github.com/contributte/event-dispatcher-extra/tree/v0.4.3 and higher
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [new RenameClassConstFetch('Contributte\\Events\\Extra\\Event\\Security\\LoggedInEvent', 'NAME', 'class'), new RenameClassConstFetch('Contributte\\Events\\Extra\\Event\\Security\\LoggedOutEvent', 'NAME', 'class'), new RenameClassConstFetch('Contributte\\Events\\Extra\\Event\\Application\\ShutdownEvent', 'NAME', 'class')]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        # nextras/forms was split into 2 packages
        'Nextras\\FormComponents\\Controls\\DatePicker' => 'Nextras\\FormComponents\\Controls\\DateControl',
        # @see https://github.com/nette/di/commit/a0d361192f8ac35f1d9f82aab7eb351e4be395ea
        'Nette\\DI\\ServiceDefinition' => 'Nette\\DI\\Definitions\\ServiceDefinition',
        'Nette\\DI\\Statement' => 'Nette\\DI\\Definitions\\Statement',
        'WebChemistry\\Forms\\Controls\\Multiplier' => 'Contributte\\FormMultiplier\\Multiplier',
    ]);
    $rectorConfig->ruleWithConfiguration(ReplaceArgumentDefaultValueRector::class, [
        // json 2nd argument is now `int` typed
        new ReplaceArgumentDefaultValue('Nette\\Utils\\Json', 'decode', 1, \true, 'Nette\\Utils\\Json::FORCE_ARRAY'),
        // @see https://github.com/nette/forms/commit/574b97f9d5e7a902a224e57d7d584e7afc9fefec
        new ReplaceArgumentDefaultValue('Nette\\Forms\\Form', 'decode', 0, \true, 'array'),
    ]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // see https://github.com/nette/forms/commit/b99385aa9d24d729a18f6397a414ea88eab6895a
        new MethodCallRename('Nette\\Forms\\Controls\\BaseControl', 'setType', 'setHtmlType'),
        new MethodCallRename('Nette\\Forms\\Controls\\BaseControl', 'setAttribute', 'setHtmlAttribute'),
        new MethodCallRename(
            'Nette\\DI\\Definitions\\ServiceDefinition',
            # see https://github.com/nette/di/commit/1705a5db431423fc610a6f339f88dead1b5dc4fb
            'setClass',
            'setType'
        ),
        new MethodCallRename('Nette\\DI\\Definitions\\ServiceDefinition', 'getClass', 'getType'),
        new MethodCallRename('Nette\\DI\\Definitions\\Definition', 'isAutowired', 'getAutowired'),
    ]);
    $rectorConfig->rule(MagicHtmlCallToAppendAttributeRector::class);
    $rectorConfig->rule(RequestGetCookieDefaultArgumentToCoalesceRector::class);
    $rectorConfig->rule(RemoveParentAndNameFromComponentConstructorRector::class);
    $rectorConfig->rule(MoveFinalGetUserToCheckRequirementsClassMethodRector::class);
    $rectorConfig->rule(ConvertAddUploadWithThirdArgumentTrueToAddMultiUploadRector::class);
};
