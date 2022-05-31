<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

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
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/phpunit-exception.php');
    $rectorConfig->ruleWithConfiguration(\Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector::class, [
        // https://github.com/rectorphp/rector/issues/1024 - no type, $dataName
        new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('PHPUnit\\Framework\\TestCase', \Rector\Core\ValueObject\MethodName::CONSTRUCT, 2, new \PHPStan\Type\MixedType()),
    ]);
    $rectorConfig->rule(\Rector\PHPUnit\Rector\MethodCall\SpecificAssertContainsRector::class);
    $rectorConfig->rule(\Rector\PHPUnit\Rector\MethodCall\SpecificAssertInternalTypeRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\Name\RenameClassRector::class, [
        # https://github.com/sebastianbergmann/phpunit/issues/3123
        'PHPUnit_Framework_MockObject_MockObject' => 'PHPUnit\\Framework\\MockObject\\MockObject',
    ]);
    $rectorConfig->rule(\Rector\PHPUnit\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector::class, [new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('PHPUnit\\Framework\\TestCase', 'setUpBeforeClass', new \PHPStan\Type\VoidType()), new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('PHPUnit\\Framework\\TestCase', 'setUp', new \PHPStan\Type\VoidType()), new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('PHPUnit\\Framework\\TestCase', 'assertPreConditions', new \PHPStan\Type\VoidType()), new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('PHPUnit\\Framework\\TestCase', 'assertPostConditions', new \PHPStan\Type\VoidType()), new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('PHPUnit\\Framework\\TestCase', 'tearDown', new \PHPStan\Type\VoidType()), new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('PHPUnit\\Framework\\TestCase', 'tearDownAfterClass', new \PHPStan\Type\VoidType()), new \Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration('PHPUnit\\Framework\\TestCase', 'onNotSuccessfulTest', new \PHPStan\Type\VoidType())]);
};
