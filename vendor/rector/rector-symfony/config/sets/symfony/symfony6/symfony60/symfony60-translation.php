<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use PHPStan\Type\BooleanType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
// https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.0.md
// @see https://github.com/symfony/symfony/blob/6.1/.github/expected-missing-return-types.diff
return static function (RectorConfig $rectorConfig) : void {
    $iterableType = new IterableType(new MixedType(), new MixedType());
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\\Component\\Translation\\Extractor\\AbstractFileExtractor', 'canBeExtracted', new BooleanType()), new AddReturnTypeDeclaration('Symfony\\Component\\Translation\\Extractor\\AbstractFileExtractor', 'extractFromDirectory', $iterableType)]);
};
