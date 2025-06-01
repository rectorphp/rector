<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use PHPStan\Type\MixedType;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
// @see https://github.com/symfony/symfony/blob/6.4/UPGRADE-6.4.md
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\\Component\\Form\\DataTransformerInterface', 'transform', new MixedType()), new AddReturnTypeDeclaration('Symfony\\Component\\Form\\DataTransformerInterface', 'reverseTransform', new MixedType())]);
};
