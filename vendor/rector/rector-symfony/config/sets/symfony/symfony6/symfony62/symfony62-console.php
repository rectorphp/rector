<?php

declare (strict_types=1);
namespace RectorPrefix202607;

use PHPStan\Type\VoidType;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
return static function (RectorConfig $rectorConfig): void {
    // @see https://github.com/symfony/symfony/pull/49347
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\Component\Console\Command\Command', 'configure', new VoidType())]);
};
