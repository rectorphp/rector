<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\StaticTypeMapper\ValueObject\Type\SimpleStaticType;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
// https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.0.md
// @see https://github.com/symfony/symfony/blob/6.1/.github/expected-missing-return-types.diff
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\\Component\\OptionsResolver\\OptionsResolver', 'setNormalizer', new SimpleStaticType('Symfony\\Component\\OptionsResolver\\OptionsResolver')), new AddReturnTypeDeclaration('Symfony\\Component\\OptionsResolver\\OptionsResolver', 'setAllowedValues', new SimpleStaticType('Symfony\\Component\\OptionsResolver\\OptionsResolver')), new AddReturnTypeDeclaration('Symfony\\Component\\OptionsResolver\\OptionsResolver', 'addAllowedValues', new SimpleStaticType('Symfony\\Component\\OptionsResolver\\OptionsResolver')), new AddReturnTypeDeclaration('Symfony\\Component\\OptionsResolver\\OptionsResolver', 'setAllowedTypes', new SimpleStaticType('Symfony\\Component\\OptionsResolver\\OptionsResolver')), new AddReturnTypeDeclaration('Symfony\\Component\\OptionsResolver\\OptionsResolver', 'addAllowedTypes', new SimpleStaticType('Symfony\\Component\\OptionsResolver\\OptionsResolver'))]);
};
