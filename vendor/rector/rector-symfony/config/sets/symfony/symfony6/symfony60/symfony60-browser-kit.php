<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
// https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.0.md
// @see https://github.com/symfony/symfony/blob/6.1/.github/expected-missing-return-types.diff
return static function (RectorConfig $rectorConfig) : void {
    $browserKitResponseType = new ObjectType('Symfony\\Component\\BrowserKit\\Response');
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\\Component\\BrowserKit\\AbstractBrowser', 'doRequestInProcess', new ObjectWithoutClassType()), new AddReturnTypeDeclaration('Symfony\\Component\\BrowserKit\\AbstractBrowser', 'doRequest', new ObjectWithoutClassType()), new AddReturnTypeDeclaration('Symfony\\Component\\BrowserKit\\AbstractBrowser', 'filterRequest', new ObjectWithoutClassType()), new AddReturnTypeDeclaration('Symfony\\Component\\BrowserKit\\AbstractBrowser', 'filterResponse', $browserKitResponseType)]);
};
