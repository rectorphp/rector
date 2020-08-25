<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Generic\ValueObject\MethodReturnType;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\SymfonyPhpConfig\Rector\ArrayItem\ReplaceArrayWithObjectRector;
use Rector\TypeDeclaration\ValueObject\ParameterTypehint;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ReplaceArrayWithObjectRector::class)
        ->call('configure', [[
            ReplaceArrayWithObjectRector::CONSTANT_NAMES_TO_VALUE_OBJECTS => [
                // 'Rector\Renaming\Rector\MethodCall\RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS' => MethodCallRename::class,
                'Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector::PARAMETER_TYPEHINTS' => ParameterTypehint::class,
                'Rector\Generic\Rector\ClassMethod\AddReturnTypeDeclarationRector::METHOD_RETURN_TYPES' => MethodReturnType::class,
            ],
        ]]);

    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    $parameters->set(Option::PATHS, [
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/rules',
        __DIR__ . '/utils',
        __DIR__ . '/packages',
        __DIR__ . '/bin/rector',
    ]);

    $parameters->set(Option::EXCLUDE_PATHS, [
        '/Source/',
        '/*Source/',
        '/Fixture/',
        '/Expected/',
        __DIR__ . '/packages/doctrine-annotation-generated/src/*',
        __DIR__ . '/packages/rector-generator/templates/*',
        '*.php.inc',
    ]);

    # so Rector code is still PHP 7.2 compatible
    $parameters->set(Option::PHP_VERSION_FEATURES, '7.2');
};
