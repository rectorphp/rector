<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use PHPStan\Type\ObjectType;
# https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.1.md
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\String_\RenameStringRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;
use Rector\Symfony\Rector\Class_\LogoutHandlerToLogoutEventSubscriberRector;
use Rector\Symfony\Rector\Class_\LogoutSuccessHandlerToLogoutEventSubscriberRector;
use Rector\Symfony\Rector\ClassMethod\RouteCollectionBuilderToRoutingConfiguratorRector;
use Rector\Transform\Rector\New_\NewArgToMethodCallRector;
use Rector\Transform\Rector\StaticCall\StaticCallToNewRector;
use Rector\Transform\ValueObject\NewArgToMethodCall;
use Rector\Transform\ValueObject\StaticCallToNew;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    // see https://github.com/symfony/symfony/pull/36243
    $rectorConfig->rule(\Rector\Symfony\Rector\Class_\LogoutHandlerToLogoutEventSubscriberRector::class);
    $rectorConfig->rule(\Rector\Symfony\Rector\Class_\LogoutSuccessHandlerToLogoutEventSubscriberRector::class);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\Name\RenameClassRector::class, [
        'Symfony\\Component\\EventDispatcher\\LegacyEventDispatcherProxy' => 'Symfony\\Component\\EventDispatcher\\EventDispatcherInterface',
        'Symfony\\Component\\Form\\Extension\\Validator\\Util\\ServerParams' => 'Symfony\\Component\\Form\\Util\\ServerParams',
        // see https://github.com/symfony/symfony/pull/35092
        'Symfony\\Component\\Inflector' => 'Symfony\\Component\\String\\Inflector\\InflectorInterface',
    ]);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class, [new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\Config\\Definition\\BaseNode', 'getDeprecationMessage', 'getDeprecation'), new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\DependencyInjection\\Definition', 'getDeprecationMessage', 'getDeprecation'), new \Rector\Renaming\ValueObject\MethodCallRename('Symfony\\Component\\DependencyInjection\\Alias', 'getDeprecationMessage', 'getDeprecation')]);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\FuncCall\RenameFunctionRector::class, ['Symfony\\Component\\DependencyInjection\\Loader\\Configuraton\\inline' => 'Symfony\\Component\\DependencyInjection\\Loader\\Configuraton\\inline_service', 'Symfony\\Component\\DependencyInjection\\Loader\\Configuraton\\ref' => 'Symfony\\Component\\DependencyInjection\\Loader\\Configuraton\\service']);
    // https://github.com/symfony/symfony/pull/35308
    $rectorConfig->ruleWithConfiguration(\Rector\Transform\Rector\New_\NewArgToMethodCallRector::class, [new \Rector\Transform\ValueObject\NewArgToMethodCall('Symfony\\Component\\Dotenv\\Dotenv', \true, 'usePutenv')]);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector::class, [new \Rector\Renaming\ValueObject\RenameClassAndConstFetch('Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer', 'ROUND_FLOOR', 'NumberFormatter', 'ROUND_FLOOR'), new \Rector\Renaming\ValueObject\RenameClassAndConstFetch('Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer', 'ROUND_DOWN', 'NumberFormatter', 'ROUND_DOWN'), new \Rector\Renaming\ValueObject\RenameClassAndConstFetch('Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer', 'ROUND_HALF_DOWN', 'NumberFormatter', 'ROUND_HALFDOWN'), new \Rector\Renaming\ValueObject\RenameClassAndConstFetch('Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer', 'ROUND_HALF_EVEN', 'NumberFormatter', 'ROUND_HALFEVEN'), new \Rector\Renaming\ValueObject\RenameClassAndConstFetch('Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer', 'ROUND_HALFUP', 'NumberFormatter', 'ROUND_HALFUP'), new \Rector\Renaming\ValueObject\RenameClassAndConstFetch('Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer', 'ROUND_UP', 'NumberFormatter', 'ROUND_UP'), new \Rector\Renaming\ValueObject\RenameClassAndConstFetch('Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer', 'ROUND_CEILING', 'NumberFormatter', 'ROUND_CEILING')]);
    // see https://github.com/symfony/symfony/pull/36943
    $rectorConfig->ruleWithConfiguration(\Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector::class, [new \Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration('Symfony\\Bundle\\FrameworkBundle\\Kernel\\MicroKernelTrait', 'configureRoutes', 0, new \PHPStan\Type\ObjectType('Symfony\\Component\\Routing\\Loader\\Configurator\\RoutingConfigurator'))]);
    $rectorConfig->ruleWithConfiguration(\Rector\Transform\Rector\StaticCall\StaticCallToNewRector::class, [new \Rector\Transform\ValueObject\StaticCallToNew('Symfony\\Component\\HttpFoundation\\Response', 'create'), new \Rector\Transform\ValueObject\StaticCallToNew('Symfony\\Component\\HttpFoundation\\JsonResponse', 'create'), new \Rector\Transform\ValueObject\StaticCallToNew('Symfony\\Component\\HttpFoundation\\RedirectResponse', 'create'), new \Rector\Transform\ValueObject\StaticCallToNew('Symfony\\Component\\HttpFoundation\\StreamedResponse', 'create')]);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\String_\RenameStringRector::class, [
        // @see https://github.com/symfony/symfony/pull/35858
        'ROLE_PREVIOUS_ADMIN' => 'IS_IMPERSONATOR',
    ]);
    $rectorConfig->rule(\Rector\Symfony\Rector\ClassMethod\RouteCollectionBuilderToRoutingConfiguratorRector::class);
};
