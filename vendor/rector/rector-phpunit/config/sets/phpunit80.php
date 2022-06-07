<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use PHPStan\Type\MixedType;
use PHPStan\Type\VoidType;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\MethodName;
use Rector\PHPUnit\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector;
use Rector\PHPUnit\Rector\MethodCall\SpecificAssertContainsRector;
use Rector\PHPUnit\Rector\MethodCall\SpecificAssertInternalTypeRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/phpunit-exception.php');
    $rectorConfig->ruleWithConfiguration(AddParamTypeDeclarationRector::class, [
        // https://github.com/rectorphp/rector/issues/1024 - no type, $dataName
        new AddParamTypeDeclaration('RectorPrefix20220607\\PHPUnit\\Framework\\TestCase', MethodName::CONSTRUCT, 2, new MixedType()),
    ]);
    $rectorConfig->rule(SpecificAssertContainsRector::class);
    $rectorConfig->rule(SpecificAssertInternalTypeRector::class);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        # https://github.com/sebastianbergmann/phpunit/issues/3123
        'PHPUnit_Framework_MockObject_MockObject' => 'RectorPrefix20220607\\PHPUnit\\Framework\\MockObject\\MockObject',
    ]);
    $rectorConfig->rule(AssertEqualsParameterToSpecificMethodsTypeRector::class);
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('RectorPrefix20220607\\PHPUnit\\Framework\\TestCase', 'setUpBeforeClass', new VoidType()), new AddReturnTypeDeclaration('RectorPrefix20220607\\PHPUnit\\Framework\\TestCase', 'setUp', new VoidType()), new AddReturnTypeDeclaration('RectorPrefix20220607\\PHPUnit\\Framework\\TestCase', 'assertPreConditions', new VoidType()), new AddReturnTypeDeclaration('RectorPrefix20220607\\PHPUnit\\Framework\\TestCase', 'assertPostConditions', new VoidType()), new AddReturnTypeDeclaration('RectorPrefix20220607\\PHPUnit\\Framework\\TestCase', 'tearDown', new VoidType()), new AddReturnTypeDeclaration('RectorPrefix20220607\\PHPUnit\\Framework\\TestCase', 'tearDownAfterClass', new VoidType()), new AddReturnTypeDeclaration('RectorPrefix20220607\\PHPUnit\\Framework\\TestCase', 'onNotSuccessfulTest', new VoidType())]);
};
