<?php

return static function (
    \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator
): void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector::class)->call(
        'configure',
        [[
            \Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector::PARAMETER_TYPEHINTS => \Symplify\SymfonyPhpConfig\ValueObjectInliner::inline([
                
























                new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration(
                    \Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeDeclarationRector\Contract\ParentInterfaceWithChangeTypeInterface::class,
                    'process',
                    0,
                    new \PHPStan\Type\StringType()
                ),
                new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration(
                    \Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeDeclarationRector\Source\ParserInterface::class,
                    'parse',
                    0,
                    new \PHPStan\Type\StringType()
                ),
                new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration(
                    \Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeDeclarationRector\Source\ClassMetadataFactory::class,
                    'setEntityManager',
                    0,
                    new \PHPStan\Type\ObjectType('Doctrine\ORM\EntityManagerInterface')
                ),
























                
            ]),
        ]]
    );
};
