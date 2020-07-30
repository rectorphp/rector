<?php

declare(strict_types=1);

use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# see https://symfony.com/blog/symfony-type-declarations-return-types-and-phpunit-compatibility

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddParamTypeDeclarationRector::class)
        ->call('configure', [[
            AddParamTypeDeclarationRector::TYPEHINT_FOR_PARAMETER_BY_METHOD_BY_CLASS => [
                'Symfony\Component\EventDispatcher\EventDispatcherInterface' => [
                    'addListener' => [
                        # see https://github.com/symfony/symfony/issues/32179:
                        'string',
                        2 => 'int',
                    ],
                    'removeListener' => ['string'],
                    'getListeners' => ['string'],
                    'getListenerPriority' => ['string'],
                    'hasListeners' => ['string'],
                ],
                'Symfony\Component\Console\Application' => [
                    'setCatchExceptions' => ['bool'],
                    'setAutoExit' => ['bool'],
                    'setName' => ['string'],
                    'setVersion' => ['string'],
                    'register' => ['string'],
                    'get' => ['string'],
                    'has' => ['string'],
                    'findNamespace' => ['string'],
                    'find' => ['string'],
                    'all' => ['string'],
                    'getAbbreviations' => ['array'],
                    'extractNamespace' => ['string', 'int'],
                    'setDefaultCommand' => ['string', 'bool'],
                ],
                'Symfony\Component\Console\Command\Command' => [
                    'mergeApplicationDefinition' => ['bool'],
                    'addArgument' => ['string', 'int', 'string'],
                    'addOption' => [
                        'string',
                        2 => 'int',
                        3 => 'string',
                    ],
                    'setName' => ['string'],
                    'setProcessTitle' => ['string'],
                    'setHidden' => ['bool'],
                    'setDescription' => ['string'],
                    'setHelp' => ['string'],
                    'setAliases' => ['iterable'],
                    'getSynopsis' => ['bool'],
                    'addUsage' => ['string'],
                    'getHelper' => ['string'],
                ],
                'Symfony\Component\Console\CommandLoader\CommandLoaderInterface' => [
                    'get' => ['string'],
                    'has' => ['string'],
                ],
                'Symfony\Component\Console\Input\InputInterface' => [
                    'getArgument' => ['string'],
                    'setArgument' => ['string'],
                    'getOption' => ['string'],
                    'setOption' => ['string'],
                    'hasOption' => ['string'],
                    'setInteractive' => ['bool'],
                ],
                'Symfony\Component\Console\Output\OutputInterface' => [
                    'write' => [
                        1 => 'bool',
                        2 => 'int',
                    ],
                    'writeln' => [
                        1 => 'int',
                    ],
                    'setVerbosity' => ['int'],
                    'setDecorated' => ['bool'],
                ],
                'Symfony\Component\Process\Process' => [
                    'signal' => ['int'],
                    'stop' => ['float', 'int'],
                    'setTty' => ['bool'],
                    'setPty' => ['bool'],
                    'setWorkingDirectory' => ['string'],
                    'inheritEnvironmentVariables' => ['bool'],
                    'updateStatus' => ['bool'],
                ],
                'Symfony\Component\EventDispatcher\EventDispatcher' => [
                    'dispatch' => ['object'],
                ],
                'Symfony\Contracts\Translation\TranslatorInterface' => [
                    'setLocale' => ['string'],
                    'trans' => [
                        '?string',
                        2 => 'string',
                        3 => 'string',
                    ],
                ],
                'Symfony\Component\Form\AbstractExtension' => [
                    'getType' => ['string'],
                    'hasType' => ['string'],
                    'getTypeExtensions' => ['string'],
                    'hasTypeExtensions' => ['string'],
                ],
                'Symfony\Component\Form\DataMapperInterface' => [
                    'mapFormsToData' => ['iterable'],
                    'mapDataToForms' => [
                        1 => 'iterable',
                    ],
                ],
                'Symfony\Component\Form\Form' => [
                    'add' => [
                        1 => 'string',
                    ],
                    'remove' => ['string'],
                    'has' => ['string'],
                    'get' => ['string'],
                ],
                'Symfony\Component\Form\FormBuilderInterface' => [
                    'add' => [
                        1 => 'string',
                    ],
                    'create' => ['string', 'string'],
                    'get' => ['string'],
                    'remove' => ['string'],
                    'has' => ['string'],
                ],
                'Symfony\Component\Form\FormExtensionInterface' => [
                    'getTypeExtensions' => ['string'],
                    'hasTypeExtensions' => ['string'],
                ],
                'Symfony\Component\Form\FormFactory' => [
                    'create' => ['string'],
                    'createNamed' => ['string', 'string'],
                    'createForProperty' => ['string', 'string'],
                    'createBuilder' => ['string'],
                    'createNamedBuilder' => ['string', 'string'],
                    'createBuilderForProperty' => ['string', 'string'],
                ],
            ],
        ]]);
};
