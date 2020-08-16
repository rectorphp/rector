<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\PHPUnit\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector;
use Rector\PHPUnit\Rector\MethodCall\ReplaceAssertArraySubsetRector;
use Rector\PHPUnit\Rector\MethodCall\SpecificAssertContainsRector;
use Rector\PHPUnit\Rector\MethodCall\SpecificAssertInternalTypeRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/phpunit-exception.php');

    $services = $containerConfigurator->services();

    $services->set(AddParamTypeDeclarationRector::class)
        ->call('configure', [[
            AddParamTypeDeclarationRector::TYPEHINT_FOR_PARAMETER_BY_METHOD_BY_CLASS => [
                'PHPUnit\Framework\TestCase' => [
                    '__construct' => [
                        # https://github.com/rectorphp/rector/issues/1024
                        # no type, $dataName
                        2 => '',
                    ],
                ],
            ],
        ]]);

    $services->set(SpecificAssertContainsRector::class);

    $services->set(SpecificAssertInternalTypeRector::class);

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                # https://github.com/sebastianbergmann/phpunit/issues/3123
                'PHPUnit_Framework_MockObject_MockObject' => 'PHPUnit\Framework\MockObject\MockObject',
            ],
        ]]);

    $services->set(AssertEqualsParameterToSpecificMethodsTypeRector::class);

    $services->set(AddReturnTypeDeclarationRector::class)
        ->call('configure', [[
            AddReturnTypeDeclarationRector::TYPEHINT_FOR_METHOD_BY_CLASS => [
                'PHPUnit\Framework\TestCase' => [
                    'setUpBeforeClass' => 'void',
                    'setUp' => 'void',
                    'assertPreConditions' => 'void',
                    'assertPostConditions' => 'void',
                    'tearDown' => 'void',
                    'tearDownAfterClass' => 'void',
                    'onNotSuccessfulTest' => 'void',
                ],
            ],
        ]]);

    $services->set(ReplaceAssertArraySubsetRector::class);
};
