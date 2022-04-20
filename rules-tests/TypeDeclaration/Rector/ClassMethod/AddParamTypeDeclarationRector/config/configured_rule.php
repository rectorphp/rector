<?php

declare(strict_types=1);

use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector\Contract\ParentInterfaceWithChangeTypeInterface;
use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector\Contract\ParentTypeToMixed;
use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector\Source\ClassMetadataFactory;
use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector\Source\ParserInterface;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig
        ->ruleWithConfiguration(AddParamTypeDeclarationRector::class, [
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

    $rectorConfig->phpVersion(PhpVersionFeature::MIXED_TYPE);
};
