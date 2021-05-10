<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
use RectorPrefix20210510\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $arrayType = new ArrayType(new MixedType(), new MixedType());
    $services->set(AddReturnTypeDeclarationRector::class)->call('configure', [[AddReturnTypeDeclarationRector::METHOD_RETURN_TYPES => ValueObjectInliner::inline([new AddReturnTypeDeclaration('PhpSpec\\ObjectBehavior', 'getMatchers', $arrayType)])]]);
};
