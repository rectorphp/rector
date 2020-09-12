<?php

declare(strict_types=1);

use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# see https://symfony.com/blog/symfony-type-declarations-return-types-and-phpunit-compatibility

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddParamTypeDeclarationRector::class)
        ->call('configure', [[
            AddParamTypeDeclarationRector::PARAMETER_TYPEHINTS => inline_value_objects([
                // see https://github.com/symfony/symfony/issues/32179
                new AddParamTypeDeclaration(
                    'Symfony\Component\EventDispatcher\EventDispatcherInterface',
                    'addListener',
                    0,
                    'string'
                ),
                new AddParamTypeDeclaration(
                    'Symfony\Component\EventDispatcher\EventDispatcherInterface',
                    'addListener',
                    2,
                    'int'
                ),
                new AddParamTypeDeclaration(
                    'Symfony\Component\EventDispatcher\EventDispatcherInterface',
                    'removeListener',
                    0,
                    'string'
                ),
                new AddParamTypeDeclaration(
                    'Symfony\Component\EventDispatcher\EventDispatcherInterface',
                    'getListeners',
                    0,
                    'string'
                ),
                new AddParamTypeDeclaration(
                    'Symfony\Component\EventDispatcher\EventDispatcherInterface',
                    'getListenerPriority',
                    0,
                    'string'
                ),
                new AddParamTypeDeclaration(
                    'Symfony\Component\EventDispatcher\EventDispatcherInterface',
                    'hasListeners',
                    0,
                    'string'
                ),
                new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'setCatchExceptions', 0, 'bool'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'setAutoExit', 0, 'bool'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'setName', 0, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'setVersion', 0, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'register', 0, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'get', 0, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'has', 0, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'findNamespace', 0, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'find', 0, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'all', 0, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'getAbbreviations', 0, 'array'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'extractNamespace', 0, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'extractNamespace', 1, 'int'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'setDefaultCommand', 0, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Application', 'setDefaultCommand', 1, 'bool'),
                new AddParamTypeDeclaration(
                    'Symfony\Component\Console\Command\Command',
                    'mergeApplicationDefinition',
                    0,
                    'bool'
                ),
                new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'addArgument', 0, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'addArgument', 1, 'int'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'addArgument', 2, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'addOption', 0, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'addOption', 2, 'int'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'addOption', 3, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'setName', 0, 'string'),
                new AddParamTypeDeclaration(
                    'Symfony\Component\Console\Command\Command',
                    'setProcessTitle',
                    0,
                    'string'
                ),
                new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'setHidden', 0, 'bool'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'setDescription', 0, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'setHelp', 0, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'setAliases', 0, 'iterable'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'getSynopsis', 0, 'bool'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'addUsage', 0, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Command\Command', 'getHelper', 0, 'string'),
                new AddParamTypeDeclaration(
                    'Symfony\Component\Console\CommandLoader\CommandLoaderInterface',
                    'get',
                    0,
                    'string'
                ),
                new AddParamTypeDeclaration(
                    'Symfony\Component\Console\CommandLoader\CommandLoaderInterface',
                    'has',
                    0,
                    'string'
                ),
                new AddParamTypeDeclaration(
                    'Symfony\Component\Console\Input\InputInterface',
                    'getArgument',
                    0,
                    'string'
                ),
                new AddParamTypeDeclaration(
                    'Symfony\Component\Console\Input\InputInterface',
                    'setArgument',
                    0,
                    'string'
                ),
                new AddParamTypeDeclaration('Symfony\Component\Console\Input\InputInterface', 'getOption', 0, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Input\InputInterface', 'setOption', 0, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Input\InputInterface', 'hasOption', 0, 'string'),
                new AddParamTypeDeclaration(
                    'Symfony\Component\Console\Input\InputInterface',
                    'setInteractive',
                    0,
                    'bool'
                ),
                new AddParamTypeDeclaration('Symfony\Component\Console\Output\OutputInterface', 'write', 1, 'bool'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Output\OutputInterface', 'write', 2, 'int'),
                new AddParamTypeDeclaration('Symfony\Component\Console\Output\OutputInterface', 'writeln', 1, 'int'),
                new AddParamTypeDeclaration(
                    'Symfony\Component\Console\Output\OutputInterface',
                    'setVerbosity',
                    0,
                    'int'
                ),
                new AddParamTypeDeclaration(
                    'Symfony\Component\Console\Output\OutputInterface',
                    'setDecorated',
                    0,
                    'bool'
                ),
                new AddParamTypeDeclaration('Symfony\Component\Process\Process', 'signal', 0, 'int'),
                new AddParamTypeDeclaration('Symfony\Component\Process\Process', 'stop', 0, 'float'),
                new AddParamTypeDeclaration('Symfony\Component\Process\Process', 'stop', 1, 'int'),
                new AddParamTypeDeclaration('Symfony\Component\Process\Process', 'setTty', 0, 'bool'),
                new AddParamTypeDeclaration('Symfony\Component\Process\Process', 'setPty', 0, 'bool'),
                new AddParamTypeDeclaration('Symfony\Component\Process\Process', 'setWorkingDirectory', 0, 'string'),
                new AddParamTypeDeclaration(
                    'Symfony\Component\Process\Process',
                    'inheritEnvironmentVariables',
                    0,
                    'bool'
                ),
                new AddParamTypeDeclaration('Symfony\Component\Process\Process', 'updateStatus', 0, 'bool'),
                new AddParamTypeDeclaration(
                    'Symfony\Component\EventDispatcher\EventDispatcher',
                    'dispatch',
                    0,
                    'object'
                ),
                new AddParamTypeDeclaration(
                    'Symfony\Contracts\Translation\TranslatorInterface',
                    'setLocale',
                    0,
                    'string'
                ),
                new AddParamTypeDeclaration('Symfony\Contracts\Translation\TranslatorInterface', 'trans', 0, '?string'),
                new AddParamTypeDeclaration('Symfony\Contracts\Translation\TranslatorInterface', 'trans', 2, 'string'),
                new AddParamTypeDeclaration('Symfony\Contracts\Translation\TranslatorInterface', 'trans', 3, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Form\AbstractExtension', 'getType', 0, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Form\AbstractExtension', 'hasType', 0, 'string'),
                new AddParamTypeDeclaration(
                    'Symfony\Component\Form\AbstractExtension',
                    'getTypeExtensions',
                    0,
                    'string'
                ),
                new AddParamTypeDeclaration(
                    'Symfony\Component\Form\AbstractExtension',
                    'hasTypeExtensions',
                    0,
                    'string'
                ),
                new AddParamTypeDeclaration(
                    'Symfony\Component\Form\DataMapperInterface',
                    'mapFormsToData',
                    0,
                    'iterable'
                ),
                new AddParamTypeDeclaration(
                    'Symfony\Component\Form\DataMapperInterface',
                    'mapDataToForms',
                    1,
                    'iterable'
                ),
                new AddParamTypeDeclaration('Symfony\Component\Form\Form', 'add', 1, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Form\Form', 'remove', 0, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Form\Form', 'has', 0, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Form\Form', 'get', 0, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Form\FormBuilderInterface', 'add', 1, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Form\FormBuilderInterface', 'create', 0, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Form\FormBuilderInterface', 'create', 1, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Form\FormBuilderInterface', 'get', 0, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Form\FormBuilderInterface', 'remove', 0, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Form\FormBuilderInterface', 'has', 0, 'string'),
                new AddParamTypeDeclaration(
                    'Symfony\Component\Form\FormExtensionInterface',
                    'getTypeExtensions',
                    0,
                    'string'
                ),
                new AddParamTypeDeclaration(
                    'Symfony\Component\Form\FormExtensionInterface',
                    'hasTypeExtensions',
                    0,
                    'string'
                ),
                new AddParamTypeDeclaration('Symfony\Component\Form\FormFactory', 'create', 0, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Form\FormFactory', 'createNamed', 0, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Form\FormFactory', 'createNamed', 1, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Form\FormFactory', 'createForProperty', 0, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Form\FormFactory', 'createForProperty', 1, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Form\FormFactory', 'createBuilder', 0, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Form\FormFactory', 'createNamedBuilder', 0, 'string'),
                new AddParamTypeDeclaration('Symfony\Component\Form\FormFactory', 'createNamedBuilder', 1, 'string'),
                new AddParamTypeDeclaration(
                    'Symfony\Component\Form\FormFactory',
                    'createBuilderForProperty',
                    0,
                    'string'
                ),
                new AddParamTypeDeclaration(
                    'Symfony\Component\Form\FormFactory',
                    'createBuilderForProperty',
                    1,
                    'string'
                ), ]
            ),
        ]]);
};
