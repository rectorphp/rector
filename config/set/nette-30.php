<?php

declare(strict_types=1);

use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;

use Rector\Generic\Rector\ClassMethod\ArgumentDefaultValueReplacerRector;
use Rector\Generic\Rector\MethodCall\FormerNullableArgumentToScalarTypedRector;
use Rector\Generic\ValueObject\ReplacedArgument;
use Rector\Nette\Rector\MethodCall\AddDatePickerToDateControlRector;
use Rector\Nette\Rector\MethodCall\BuilderExpandToHelperExpandRector;
use Rector\Nette\Rector\MethodCall\GetConfigWithDefaultsArgumentToArrayMergeInCompilerExtensionRector;
use Rector\Nette\Rector\MethodCall\MagicHtmlCallToAppendAttributeRector;
use Rector\Nette\Rector\MethodCall\SetClassWithArgumentToSetFactoryRector;
use Rector\NetteCodeQuality\Rector\ArrayDimFetch\ChangeFormArrayAccessToAnnotatedControlVariableRector;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstantRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\ClassConstantRename;
use Rector\Renaming\ValueObject\MethodCallRename;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\Transform\Rector\StaticCall\StaticCallToMethodCallRector;
use Rector\Transform\ValueObject\StaticCallToMethodCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/nette-30-return-types.php');

    $containerConfigurator->import(__DIR__ . '/nette-30-param-types.php');

    $services = $containerConfigurator->services();

    $services->set(AddDatePickerToDateControlRector::class);

    $services->set(SetClassWithArgumentToSetFactoryRector::class);

    $services->set(ChangeFormArrayAccessToAnnotatedControlVariableRector::class);

    $services->set(GetConfigWithDefaultsArgumentToArrayMergeInCompilerExtensionRector::class);

    // Control class has remove __construct(), e.g. https://github.com/Pixidos/GPWebPay/pull/16/files#diff-fdc8251950f85c5467c63c249df05786
    $services->set(RemoveParentCallWithoutParentRector::class);

    // https://github.com/nette/utils/commit/d0041ba59f5d8bf1f5b3795fd76d43fb13ea2e15
    $services->set(FormerNullableArgumentToScalarTypedRector::class);

    $services->set(StaticCallToMethodCallRector::class)
        ->call('configure', [[
            StaticCallToMethodCallRector::STATIC_CALLS_TO_METHOD_CALLS => inline_value_objects([
                new StaticCallToMethodCall('Nette\Security\Passwords', 'hash', 'Nette\Security\Passwords', 'hash'),
                new StaticCallToMethodCall(
                    'Nette\Security\Passwords',
                    'verify',
                    'Nette\Security\Passwords',
                    'verify'
                ),
                new StaticCallToMethodCall(
                    'Nette\Security\Passwords',
                    'needsRehash',
                    'Nette\Security\Passwords',
                    'needsRehash'
                ),
            ]),
        ]]);

    // https://github.com/contributte/event-dispatcher-extra/tree/v0.4.3 and higher
    $services->set(RenameClassConstantRector::class)
        ->call('configure', [[
            RenameClassConstantRector::CLASS_CONSTANT_RENAME => inline_value_objects([
                new ClassConstantRename('Contributte\Events\Extra\Event\Security\LoggedInEvent', 'NAME', 'class'),
                new ClassConstantRename('Contributte\Events\Extra\Event\Security\LoggedOutEvent', 'NAME', 'class'),
                new ClassConstantRename('Contributte\Events\Extra\Event\Application\ShutdownEvent', 'NAME', 'class'),
            ]),
        ]]);

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                # nextras/forms was split into 2 packages
                'Nextras\FormComponents\Controls\DatePicker' => 'Nextras\FormComponents\Controls\DateControl',
                # @see https://github.com/nette/di/commit/a0d361192f8ac35f1d9f82aab7eb351e4be395ea
                'Nette\DI\ServiceDefinition' => 'Nette\DI\Definitions\ServiceDefinition',
                'Nette\DI\Statement' => 'Nette\DI\Definitions\Statement',
            ],
        ]]);

    $services->set(BuilderExpandToHelperExpandRector::class);

    $services->set(ArgumentDefaultValueReplacerRector::class)
        ->call('configure', [[
            ArgumentDefaultValueReplacerRector::REPLACED_ARGUMENTS => inline_value_objects([
                // json 2nd argument is now int typed
                new ReplacedArgument('Nette\Utils\Json', 'decode', 1, true, 'Nette\Utils\Json::FORCE_ARRAY'),
                // @see https://github.com/nette/forms/commit/574b97f9d5e7a902a224e57d7d584e7afc9fefec
                new ReplacedArgument('Nette\Forms\Form', 'decode', 0, true, 'array'),
            ]),
        ]]
    );

    $services->set(RenameMethodRector::class)
        // @see https://github.com/nette/forms/commit/b99385aa9d24d729a18f6397a414ea88eab6895a
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => inline_value_objects([
                new MethodCallRename('Nette\Forms\Controls\BaseControl', 'setAttribute', 'setHtmlAttribute'),
            ]),
        ]]);

    $services->set(RenameMethodRector::class)->call('configure', [[
        RenameMethodRector::METHOD_CALL_RENAMES => inline_value_objects([
            new MethodCallRename(
                'Nette\Forms\Controls\BaseControl',
                # see https://github.com/nette/forms/commit/b99385aa9d24d729a18f6397a414ea88eab6895a
                'setType',
                'setHtmlType'
            ),
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
};
