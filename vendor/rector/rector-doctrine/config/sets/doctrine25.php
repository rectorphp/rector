<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector;
use RectorPrefix20220606\Rector\Removing\ValueObject\ArgumentRemover;
use RectorPrefix20220606\Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use RectorPrefix20220606\Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('Doctrine\\ORM\\Mapping\\ClassMetadataFactory', 'setEntityManager', 0, new ObjectType('Doctrine\\ORM\\EntityManagerInterface')), new AddParamTypeDeclaration('Doctrine\\ORM\\Tools\\DebugUnitOfWorkListener', 'dumpIdentityMap', 0, new ObjectType('Doctrine\\ORM\\EntityManagerInterface'))]);
    $rectorConfig->ruleWithConfiguration(ArgumentRemoverRector::class, [new ArgumentRemover('Doctrine\\ORM\\Persisters\\Entity\\AbstractEntityInheritancePersister', 'getSelectJoinColumnSQL', 4, null)]);
};
