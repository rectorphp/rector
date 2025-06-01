<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
// https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.0.md
// @see https://github.com/symfony/symfony/blob/6.1/.github/expected-missing-return-types.diff
return static function (RectorConfig $rectorConfig) : void {
    $iterableType = new IterableType(new MixedType(), new MixedType());
    $arrayType = new ArrayType(new MixedType(), new MixedType());
    $httpFoundationResponseType = new ObjectType('Symfony\\Component\\HttpFoundation\\Response');
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\KernelInterface', 'registerBundles', $iterableType), new AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\CacheWarmer\\CacheWarmerInterface', 'isOptional', new BooleanType()), new AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\CacheWarmer\\WarmableInterface', 'warmUp', $arrayType), new AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\DataCollector\\DataCollector', 'getCasters', $arrayType), new AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\DataCollector\\DataCollectorInterface', 'getName', new StringType()), new AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\HttpCache\\HttpCache', 'forward', $httpFoundationResponseType), new AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\HttpKernelBrowser', 'doRequest', $httpFoundationResponseType), new AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\HttpKernelBrowser', 'getScript', new StringType()), new AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\Log\\DebugLoggerInterface', 'getLogs', $arrayType), new AddReturnTypeDeclaration('Symfony\\Component\\HttpKernel\\Log\\DebugLoggerInterface', 'countErrors', new IntegerType())]);
};
