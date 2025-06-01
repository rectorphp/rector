<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use PHPStan\Type\ObjectType;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(AddParamTypeDeclarationRector::class, [
        // @see https://github.com/symfony/symfony/commit/ce77be2507631cd12e4ca37510dab37f4c2b759a
        new AddParamTypeDeclaration('Symfony\\Component\\Form\\DataMapperInterface', 'mapFormsToData', 0, new ObjectType(\Traversable::class)),
        // @see https://github.com/symfony/symfony/commit/ce77be2507631cd12e4ca37510dab37f4c2b759a
        new AddParamTypeDeclaration('Symfony\\Component\\Form\\DataMapperInterface', 'mapDataToForms', 1, new ObjectType(\Traversable::class)),
    ]);
};
