<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\VoidType;
use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Core\ValueObject\MethodName;
use RectorPrefix20220606\Rector\PHPUnit\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector;
use RectorPrefix20220606\Rector\PHPUnit\Rector\MethodCall\SpecificAssertContainsRector;
use RectorPrefix20220606\Rector\PHPUnit\Rector\MethodCall\SpecificAssertInternalTypeRector;
use RectorPrefix20220606\Rector\Renaming\Rector\Name\RenameClassRector;
use RectorPrefix20220606\Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use RectorPrefix20220606\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use RectorPrefix20220606\Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use RectorPrefix20220606\Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/phpunit-exception.php');
    $rectorConfig->ruleWithConfiguration(AddParamTypeDeclarationRector::class, [
        // https://github.com/rectorphp/rector/issues/1024 - no type, $dataName
        new AddParamTypeDeclaration('PHPUnit\\Framework\\TestCase', MethodName::CONSTRUCT, 2, new MixedType()),
    ]);
    $rectorConfig->rule(SpecificAssertContainsRector::class);
    $rectorConfig->rule(SpecificAssertInternalTypeRector::class);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        # https://github.com/sebastianbergmann/phpunit/issues/3123
        'PHPUnit_Framework_MockObject_MockObject' => 'PHPUnit\\Framework\\MockObject\\MockObject',
    ]);
    $rectorConfig->rule(AssertEqualsParameterToSpecificMethodsTypeRector::class);
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('PHPUnit\\Framework\\TestCase', 'setUpBeforeClass', new VoidType()), new AddReturnTypeDeclaration('PHPUnit\\Framework\\TestCase', 'setUp', new VoidType()), new AddReturnTypeDeclaration('PHPUnit\\Framework\\TestCase', 'assertPreConditions', new VoidType()), new AddReturnTypeDeclaration('PHPUnit\\Framework\\TestCase', 'assertPostConditions', new VoidType()), new AddReturnTypeDeclaration('PHPUnit\\Framework\\TestCase', 'tearDown', new VoidType()), new AddReturnTypeDeclaration('PHPUnit\\Framework\\TestCase', 'tearDownAfterClass', new VoidType()), new AddReturnTypeDeclaration('PHPUnit\\Framework\\TestCase', 'onNotSuccessfulTest', new VoidType())]);
};
