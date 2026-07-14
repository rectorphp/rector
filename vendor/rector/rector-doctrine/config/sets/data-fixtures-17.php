<?php

declare (strict_types=1);
namespace RectorPrefix202607;

use PHPStan\Type\IntegerType;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Doctrine\Common\DataFixtures\OrderedFixtureInterface', 'getOrder', new IntegerType())]);
};
