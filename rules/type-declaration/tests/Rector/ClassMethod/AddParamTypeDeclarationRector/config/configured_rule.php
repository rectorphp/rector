<?php

use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeDeclarationRector\Contract\ParentInterfaceWithChangeTypeInterface;
use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeDeclarationRector\Source\ClassMetadataFactory;
use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeDeclarationRector\Source\ParserInterface;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(AddParamTypeDeclarationRector::class)->call(
        'configure',
        [[
            AddParamTypeDeclarationRector::PARAMETER_TYPEHINTS => ValueObjectInliner::inline([

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

            ]),
        ]]
    );
};
