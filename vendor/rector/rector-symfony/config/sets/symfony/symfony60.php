<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

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
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->sets([SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES]);
    $rectorConfig->import(__DIR__ . '/symfony6/symfony-return-types.php');
    $rectorConfig->ruleWithConfiguration(ReplaceServiceArgumentRector::class, [new ReplaceServiceArgument('RectorPrefix20220607\\Psr\\Container\\ContainerInterface', new String_('service_container')), new ReplaceServiceArgument('RectorPrefix20220607\\Symfony\\Component\\DependencyInjection\\ContainerInterface', new String_('service_container'))]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/39484
        'RectorPrefix20220607\\Symfony\\Contracts\\HttpClient\\HttpClientInterface\\RemoteJsonManifestVersionStrategy' => 'RectorPrefix20220607\\Symfony\\Component\\Asset\\VersionStrategy\\JsonManifestVersionStrategy',
    ]);
    $rectorConfig->ruleWithConfiguration(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('RectorPrefix20220607\\Symfony\\Component\\Config\\Loader\\LoaderInterface', 'load', 0, new MixedType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Symfony\\Bundle\\FrameworkBundle\\Kernel\\MicroKernelTrait', 'configureRoutes', 0, new ObjectType('RectorPrefix20220607\\Symfony\\Component\\Routing\\Loader\\Configurator\\RoutingConfigurator'))]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // @see https://github.com/symfony/symfony/pull/40403
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Bridge\\Doctrine\\Security\\User\\UserLoaderInterface', 'loadUserByUsername', 'loadUserByIdentifier'),
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Security\\Core\\User\\UserProviderInterface', 'loadUserByUsername', 'loadUserByIdentifier'),
        // @see https://github.com/rectorphp/rector-symfony/issues/112
        new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Security\\Core\\User\\UserInterface', 'getUsername', 'getUserIdentifier'),
    ]);
    $rectorConfig->rule(GetHelperControllerToServiceRector::class);
};
