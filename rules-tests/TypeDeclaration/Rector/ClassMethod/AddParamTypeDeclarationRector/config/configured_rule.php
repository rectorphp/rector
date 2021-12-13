<?php

declare(strict_types=1);

use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector\Contract\ParentInterfaceWithChangeTypeInterface;
use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector\Contract\ParentTypeToMixed;
use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector\Source\ClassMetadataFactory;
use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector\Source\ParserInterface;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddParamTypeDeclarationRector::class)
        ->configure([
            new AddParamTypeDeclaration(
                ParentInterfaceWithChangeTypeInterface::class,
                'process',
                0,
                new StringType()
            ),
            new AddParamTypeDeclaration(ParserInterface::class, 'parse', 0, new StringType()),
            new AddParamTypeDeclaration(
                ClassMetadataFactory::class,
                'setEntityManager',
                0,
                new ObjectType('Doctrine\ORM\EntityManagerInterface')
            ),
            new AddParamTypeDeclaration(ParentTypeToMixed::class, 'process', 0, new MixedType(true)),
        ]);

    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersionFeature::MIXED_TYPE);
};
