<?php

declare (strict_types=1);
namespace RectorPrefix202607;

use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Twig\Extension\ExtensionInterface', 'getFilters', new ArrayType(new MixedType(), new MixedType())), new AddReturnTypeDeclaration('Twig\Extension\ExtensionInterface', 'getTests', new ArrayType(new MixedType(), new MixedType())), new AddReturnTypeDeclaration('Twig\Extension\ExtensionInterface', 'getFunctions', new ArrayType(new MixedType(), new MixedType()))]);
};
