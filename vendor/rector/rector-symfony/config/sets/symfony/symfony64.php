<?php

declare (strict_types=1);
namespace RectorPrefix202407;

use PHPStan\Type\MixedType;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Class_\RenameAttributeRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\RenameAttribute;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
// @see https://github.com/symfony/symfony/blob/6.4/UPGRADE-6.4.md
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Symfony\\Component\\HttpKernel\\UriSigner' => 'Symfony\\Component\\HttpFoundation\\UriSigner', 'Symfony\\Component\\HttpKernel\\Debug\\FileLinkFormatter' => 'Symfony\\Component\\ErrorHandler\\ErrorRenderer\\FileLinkFormatter']);
    $rectorConfig->ruleWithConfiguration(RenameAttributeRector::class, [new RenameAttribute('Symfony\\Component\\Routing\\Annotation\\Route', 'Symfony\\Component\\Routing\\Attribute\\Route')]);
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\\Component\\Form\\DataTransformerInterface', 'transform', new MixedType()), new AddReturnTypeDeclaration('Symfony\\Component\\Form\\DataTransformerInterface', 'reverseTransform', new MixedType())]);
};
