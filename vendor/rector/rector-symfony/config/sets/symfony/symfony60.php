<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use PhpParser\Node\Scalar\String_;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Symfony\Rector\FuncCall\ReplaceServiceArgumentRector;
use Rector\Symfony\Rector\MethodCall\GetHelperControllerToServiceRector;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Symfony\ValueObject\ReplaceServiceArgument;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
# https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.0.md
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->sets([\Rector\Symfony\Set\SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES]);
    $rectorConfig->import(__DIR__ . '/symfony6/symfony-return-types.php');
    $rectorConfig->ruleWithConfiguration(\Rector\Symfony\Rector\FuncCall\ReplaceServiceArgumentRector::class, [new \Rector\Symfony\ValueObject\ReplaceServiceArgument('Psr\\Container\\ContainerInterface', new \PhpParser\Node\Scalar\String_('service_container')), new \Rector\Symfony\ValueObject\ReplaceServiceArgument('Symfony\\Component\\DependencyInjection\\ContainerInterface', new \PhpParser\Node\Scalar\String_('service_container'))]);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\Name\RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/39484
        'Symfony\\Contracts\\HttpClient\\HttpClientInterface\\RemoteJsonManifestVersionStrategy' => 'Symfony\\Component\\Asset\\VersionStrategy\\JsonManifestVersionStrategy',
    ]);
    $rectorConfig->ruleWithConfiguration(\Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector::class, [new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Component\\Config\\Loader\\LoaderInterface', 'load', 0, new \PHPStan\Type\MixedType()), new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Bundle\\FrameworkBundle\\Kernel\\MicroKernelTrait', 'configureRoutes', 0, new \PHPStan\Type\ObjectType('Symfony\\Component\\Routing\\Loader\\Configurator\\RoutingConfigurator'))]);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class, [
        // @see https://github.com/symfony/symfony/pull/40403
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Bridge\\Doctrine\\Security\\User\\UserLoaderInterface', 'loadUserByUsername', 'loadUserByIdentifier'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\Security\\Core\\User\\UserProviderInterface', 'loadUserByUsername', 'loadUserByIdentifier'),
        // @see https://github.com/rectorphp/rector-symfony/issues/112
        new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\Security\\Core\\User\\UserInterface', 'getUsername', 'getUserIdentifier'),
    ]);
    $rectorConfig->rule(\Rector\Symfony\Rector\MethodCall\GetHelperControllerToServiceRector::class);
};
