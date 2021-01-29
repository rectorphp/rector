<?php

declare(strict_types=1);

use PHPStan\Type\MixedType;
use PHPStan\Type\VoidType;
use Rector\Core\ValueObject\MethodName;
use Rector\PHPUnit\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector;
use Rector\PHPUnit\Rector\MethodCall\ReplaceAssertArraySubsetWithDmsPolyfillRector;
use Rector\PHPUnit\Rector\MethodCall\SpecificAssertContainsRector;
use Rector\PHPUnit\Rector\MethodCall\SpecificAssertInternalTypeRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/phpunit-exception.php');

    $services = $containerConfigurator->services();

    $services->set(AddParamTypeDeclarationRector::class)
        ->call('configure', [[
            AddParamTypeDeclarationRector::PARAMETER_TYPEHINTS => ValueObjectInliner::inline([
                // https://github.com/rectorphp/rector/issues/1024 - no type, $dataName
                new AddParamTypeDeclaration('PHPUnit\Framework\TestCase', MethodName::CONSTRUCT, 2, new MixedType()),
            ]),
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
            AddReturnTypeDeclarationRector::METHOD_RETURN_TYPES => ValueObjectInliner::inline([
                new AddReturnTypeDeclaration('PHPUnit\Framework\TestCase', 'setUpBeforeClass', new VoidType()),
                new AddReturnTypeDeclaration('PHPUnit\Framework\TestCase', 'setUp', new VoidType()),
                new AddReturnTypeDeclaration('PHPUnit\Framework\TestCase', 'assertPreConditions', new VoidType()),
                new AddReturnTypeDeclaration('PHPUnit\Framework\TestCase', 'assertPostConditions', new VoidType()),
                new AddReturnTypeDeclaration('PHPUnit\Framework\TestCase', 'tearDown', new VoidType()),
                new AddReturnTypeDeclaration('PHPUnit\Framework\TestCase', 'tearDownAfterClass', new VoidType()),
                new AddReturnTypeDeclaration('PHPUnit\Framework\TestCase', 'onNotSuccessfulTest', new VoidType()),
            ]),
        ]]);

    $services->set(ReplaceAssertArraySubsetWithDmsPolyfillRector::class);
};
