<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
// https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.0.md
// @see https://github.com/symfony/symfony/blob/6.1/.github/expected-missing-return-types.diff
return static function (RectorConfig $rectorConfig) : void {
    $arrayType = new ArrayType(new MixedType(), new MixedType());
    $httpFoundationResponseType = new ObjectType('Symfony\\Component\\HttpFoundation\\Response');
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\\Component\\Security\\Http\\EntryPoint\\AuthenticationEntryPointInterface', 'start', $httpFoundationResponseType), new AddReturnTypeDeclaration('Symfony\\Component\\Security\\Http\\Firewall', 'getSubscribedEvents', $arrayType), new AddReturnTypeDeclaration('Symfony\\Component\\Security\\Http\\FirewallMapInterface', 'getListeners', $arrayType), new AddReturnTypeDeclaration('Symfony\\Component\\Security\\Http\\Authenticator\\AuthenticatorInterface', 'authenticate', new ObjectType('Symfony\\Component\\Security\\Http\\Authenticator\\Passport\\Passport'))]);
};
