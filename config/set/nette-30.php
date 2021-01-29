<?php

declare(strict_types=1);

use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;
use Rector\Generic\Rector\ClassMethod\ArgumentDefaultValueReplacerRector;
use Rector\Generic\ValueObject\ArgumentDefaultValueReplacer;
use Rector\Nette\Rector\Class_\MoveFinalGetUserToCheckRequirementsClassMethodRector;
use Rector\Nette\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector;
use Rector\Nette\Rector\MethodCall\AddNextrasDatePickerToDateControlRector;
use Rector\Nette\Rector\MethodCall\ConvertAddUploadWithThirdArgumentTrueToAddMultiUploadRector;
use Rector\Nette\Rector\MethodCall\MagicHtmlCallToAppendAttributeRector;
use Rector\Nette\Rector\MethodCall\MergeDefaultsInGetConfigCompilerExtensionRector;
use Rector\Nette\Rector\MethodCall\RequestGetCookieDefaultArgumentToCoalesceRector;
use Rector\NetteCodeQuality\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameClassConstFetch;
use Rector\Transform\Rector\StaticCall\StaticCallToMethodCallRector;
use Rector\Transform\ValueObject\StaticCallToMethodCall;
use Rector\TypeDeclaration\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/nette-30-composer.php');
    $containerConfigurator->import(__DIR__ . '/nette-30-dependency-injection.php');
    $containerConfigurator->import(__DIR__ . '/nette-30-return-types.php');
    $containerConfigurator->import(__DIR__ . '/nette-30-param-types.php');

    $services = $containerConfigurator->services();
    $services->set(AddNextrasDatePickerToDateControlRector::class);
    $services->set(ChangeFormArrayAccessToAnnotatedControlVariableRector::class);
    $services->set(MergeDefaultsInGetConfigCompilerExtensionRector::class);
    // Control class has remove __construct(), e.g. https://github.com/Pixidos/GPWebPay/pull/16/files#diff-fdc8251950f85c5467c63c249df05786
    $services->set(RemoveParentCallWithoutParentRector::class);
    // https://github.com/nette/utils/commit/d0041ba59f5d8bf1f5b3795fd76d43fb13ea2e15
    $services->set(FormerNullableArgumentToScalarTypedRector::class);
    $services->set(StaticCallToMethodCallRector::class)->call('configure', [[
        StaticCallToMethodCallRector::STATIC_CALLS_TO_METHOD_CALLS => ValueObjectInliner::inline([
            new StaticCallToMethodCall('Nette\Security\Passwords', 'hash', 'Nette\Security\Passwords', 'hash'),
            new StaticCallToMethodCall('Nette\Security\Passwords', 'verify', 'Nette\Security\Passwords', 'verify'),
            new StaticCallToMethodCall(
                'Nette\Security\Passwords',
                'needsRehash',
                'Nette\Security\Passwords',
                'needsRehash'
            ),
        ]),
    ]]);
    // https://github.com/contributte/event-dispatcher-extra/tree/v0.4.3 and higher
    $services->set(RenameClassConstFetchRector::class)->call('configure', [[
        RenameClassConstFetchRector::CLASS_CONSTANT_RENAME => ValueObjectInliner::inline([
            new RenameClassConstFetch('Contributte\Events\Extra\Event\Security\LoggedInEvent', 'NAME', 'class'),
            new RenameClassConstFetch('Contributte\Events\Extra\Event\Security\LoggedOutEvent', 'NAME', 'class'),
            new RenameClassConstFetch('Contributte\Events\Extra\Event\Application\ShutdownEvent', 'NAME', 'class'),
        ]),
    ]]);
    $services->set(RenameClassRector::class)->call('configure', [[
        RenameClassRector::OLD_TO_NEW_CLASSES => [
            # nextras/forms was split into 2 packages
            'Nextras\FormComponents\Controls\DatePicker' => 'Nextras\FormComponents\Controls\DateControl',
            # @see https://github.com/nette/di/commit/a0d361192f8ac35f1d9f82aab7eb351e4be395ea
            'Nette\DI\ServiceDefinition' => 'Nette\DI\Definitions\ServiceDefinition',
            'Nette\DI\Statement' => 'Nette\DI\Definitions\Statement',
            'WebChemistry\Forms\Controls\Multiplier' => 'Contributte\FormMultiplier\Multiplier',
        ],
    ]]);
    $services->set(ArgumentDefaultValueReplacerRector::class)->call('configure', [[
        ArgumentDefaultValueReplacerRector::REPLACED_ARGUMENTS => ValueObjectInliner::inline([
            // json 2nd argument is now `int` typed
            new ArgumentDefaultValueReplacer('Nette\Utils\Json', 'decode', 1, true, 'Nette\Utils\Json::FORCE_ARRAY'),
            // @see https://github.com/nette/forms/commit/574b97f9d5e7a902a224e57d7d584e7afc9fefec
            new ArgumentDefaultValueReplacer('Nette\Forms\Form', 'decode', 0, true, 'array'),
        ]),
    ]]);
    $services->set(RenameMethodRector::class)->call('configure', [[
        RenameMethodRector::METHOD_CALL_RENAMES => ValueObjectInliner::inline([
            // see https://github.com/nette/forms/commit/b99385aa9d24d729a18f6397a414ea88eab6895a
            new MethodCallRename('Nette\Forms\Controls\BaseControl', 'setType', 'setHtmlType'),
            new MethodCallRename('Nette\Forms\Controls\BaseControl', 'setAttribute', 'setHtmlAttribute'),
            new MethodCallRename(
                'Nette\DI\Definitions\ServiceDefinition',
                # see https://github.com/nette/di/commit/1705a5db431423fc610a6f339f88dead1b5dc4fb
                'setClass',
                'setType'
            ),
            new MethodCallRename('Nette\DI\Definitions\ServiceDefinition', 'getClass', 'getType'),
            new MethodCallRename('Nette\DI\Definitions\Definition', 'isAutowired', 'getAutowired'),
        ]),
    ]]);
    $services->set(MagicHtmlCallToAppendAttributeRector::class);
    $services->set(RequestGetCookieDefaultArgumentToCoalesceRector::class);
    $services->set(RemoveParentAndNameFromComponentConstructorRector::class);
    $services->set(MoveFinalGetUserToCheckRequirementsClassMethodRector::class);
    $services->set(ConvertAddUploadWithThirdArgumentTrueToAddMultiUploadRector::class);
};
