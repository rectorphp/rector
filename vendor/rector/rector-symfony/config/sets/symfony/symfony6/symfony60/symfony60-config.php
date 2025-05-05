<?php

declare (strict_types=1);
namespace RectorPrefix202505;

use PHPStan\Type\MixedType;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('Symfony\\Component\\Config\\Loader\\LoaderInterface', 'load', 0, new MixedType(\true)), new AddParamTypeDeclaration('Symfony\\Component\\Config\\Loader\\LoaderInterface', 'supports', 0, new MixedType(\true))]);
};
