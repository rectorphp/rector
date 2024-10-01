<?php

declare (strict_types=1);
namespace RectorPrefix202410;

use PHPStan\Type\ObjectType;
use Rector\Config\RectorConfig;
use Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Removing\ValueObject\ArgumentRemover;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('Doctrine\\ORM\\Mapping\\ClassMetadataFactory', 'setEntityManager', 0, new ObjectType('Doctrine\\ORM\\EntityManagerInterface')), new AddParamTypeDeclaration('Doctrine\\ORM\\Tools\\DebugUnitOfWorkListener', 'dumpIdentityMap', 0, new ObjectType('Doctrine\\ORM\\EntityManagerInterface'))]);
    $rectorConfig->ruleWithConfiguration(ArgumentRemoverRector::class, [new ArgumentRemover('Doctrine\\ORM\\Persisters\\Entity\\AbstractEntityInheritancePersister', 'getSelectJoinColumnSQL', 4, null)]);
};
