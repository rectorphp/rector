<?php

declare(strict_types=1);

use PHPStan\Type\MixedType;
use PHPStan\Type\VoidType;
use Rector\Config\RectorConfig;
use Rector\StaticTypeMapper\ValueObject\Type\SimpleStaticType;
use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector\Fixture\ReturnOfStatic;
use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector\Fixture\ReturnTheMixed;
use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector\Source\PHPUnitTestCase;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [
            new AddReturnTypeDeclaration(PHPUnitTestCase::class, 'tearDown', new VoidType()),
            new AddReturnTypeDeclaration(ReturnTheMixed::class, 'create', new MixedType(true)),
            new AddReturnTypeDeclaration(
                ReturnOfStatic::class,
                'create',
                new SimpleStaticType(ReturnOfStatic::class)
            ),
        ]);
};
