<?php

declare (strict_types=1);
namespace RectorPrefix20220418;

use PHPStan\Type\ObjectType;
use Rector\Config\RectorConfig;
use Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Removing\ValueObject\ArgumentRemover;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $services = $rectorConfig->services();
    $services->set(\Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector::class)->configure([new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Doctrine\\ORM\\Mapping\\ClassMetadataFactory', 'setEntityManager', 0, new \PHPStan\Type\ObjectType('Doctrine\\ORM\\EntityManagerInterface')), new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Doctrine\\ORM\\Tools\\DebugUnitOfWorkListener', 'dumpIdentityMap', 0, new \PHPStan\Type\ObjectType('Doctrine\\ORM\\EntityManagerInterface'))]);
    $services->set(\Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector::class)->configure([new \Rector\Removing\ValueObject\ArgumentRemover('Doctrine\\ORM\\Persisters\\Entity\\AbstractEntityInheritancePersister', 'getSelectJoinColumnSQL', 4, null)]);
};
