<?php

declare (strict_types=1);
namespace RectorPrefix20211020;

use PHPStan\Type\MixedType;
use PHPStan\Type\VoidType;
use Rector\Core\ValueObject\MethodName;
use Rector\PHPUnit\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector;
use Rector\PHPUnit\Rector\MethodCall\SpecificAssertContainsRector;
use Rector\PHPUnit\Rector\MethodCall\SpecificAssertInternalTypeRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $containerConfigurator->import(__DIR__ . '/phpunit-exception.php');
    $services = $containerConfigurator->services();
    $services->set(\Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector::class)->call('configure', [[\Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector::PARAMETER_TYPEHINTS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
        // https://github.com/rectorphp/rector/issues/1024 - no type, $dataName
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('PHPUnit\\Framework\\TestCase', \Rector\Core\ValueObject\MethodName::CONSTRUCT, 2, new \PHPStan\Type\MixedType()),
    ])]]);
    $services->set(\Rector\PHPUnit\Rector\MethodCall\SpecificAssertContainsRector::class);
    $services->set(\Rector\PHPUnit\Rector\MethodCall\SpecificAssertInternalTypeRector::class);
    $services->set(\Rector\Renaming\Rector\Name\RenameClassRector::class)->call('configure', [[\Rector\Renaming\Rector\Name\RenameClassRector::OLD_TO_NEW_CLASSES => [
        # https://github.com/sebastianbergmann/phpunit/issues/3123
        'PHPUnit_Framework_MockObject_MockObject' => 'PHPUnit\\Framework\\MockObject\\MockObject',
    ]]]);
    $services->set(\Rector\PHPUnit\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector::class);
    $services->set(\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector::class)->call('configure', [[\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector::METHOD_RETURN_TYPES => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('PHPUnit\\Framework\\TestCase', 'setUpBeforeClass', new \PHPStan\Type\VoidType()), new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('PHPUnit\\Framework\\TestCase', 'setUp', new \PHPStan\Type\VoidType()), new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('PHPUnit\\Framework\\TestCase', 'assertPreConditions', new \PHPStan\Type\VoidType()), new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('PHPUnit\\Framework\\TestCase', 'assertPostConditions', new \PHPStan\Type\VoidType()), new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('PHPUnit\\Framework\\TestCase', 'tearDown', new \PHPStan\Type\VoidType()), new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('PHPUnit\\Framework\\TestCase', 'tearDownAfterClass', new \PHPStan\Type\VoidType()), new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('PHPUnit\\Framework\\TestCase', 'onNotSuccessfulTest', new \PHPStan\Type\VoidType())])]]);
};
