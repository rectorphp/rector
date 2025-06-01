<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
// https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.0.md
// @see https://github.com/symfony/symfony/blob/6.1/.github/expected-missing-return-types.diff
return static function (RectorConfig $rectorConfig) : void {
    $routeCollectionType = new ObjectType('Symfony\\Component\\Routing\\RouteCollection');
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\\Component\\Routing\\Loader\\AnnotationClassLoader', 'getDefaultRouteName', new StringType()), new AddReturnTypeDeclaration('Symfony\\Component\\Routing\\Router', 'getRouteCollection', $routeCollectionType), new AddReturnTypeDeclaration('Symfony\\Component\\Routing\\RouterInterface', 'getRouteCollection', $routeCollectionType)]);
};
