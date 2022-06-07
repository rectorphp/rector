<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

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
return static function (RectorConfig $rectorConfig) : void {
    // see https://github.com/symfony/symfony/pull/36243
    $rectorConfig->rule(LogoutHandlerToLogoutEventSubscriberRector::class);
    $rectorConfig->rule(LogoutSuccessHandlerToLogoutEventSubscriberRector::class);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        'RectorPrefix20220607\\Symfony\\Component\\EventDispatcher\\LegacyEventDispatcherProxy' => 'RectorPrefix20220607\\Symfony\\Component\\EventDispatcher\\EventDispatcherInterface',
        'RectorPrefix20220607\\Symfony\\Component\\Form\\Extension\\Validator\\Util\\ServerParams' => 'RectorPrefix20220607\\Symfony\\Component\\Form\\Util\\ServerParams',
        // see https://github.com/symfony/symfony/pull/35092
        'RectorPrefix20220607\\Symfony\\Component\\Inflector' => 'RectorPrefix20220607\\Symfony\\Component\\String\\Inflector\\InflectorInterface',
    ]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\Config\\Definition\\BaseNode', 'getDeprecationMessage', 'getDeprecation'), new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\DependencyInjection\\Definition', 'getDeprecationMessage', 'getDeprecation'), new MethodCallRename('RectorPrefix20220607\\Symfony\\Component\\DependencyInjection\\Alias', 'getDeprecationMessage', 'getDeprecation')]);
    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, ['RectorPrefix20220607\\Symfony\\Component\\DependencyInjection\\Loader\\Configuraton\\inline' => 'RectorPrefix20220607\\Symfony\\Component\\DependencyInjection\\Loader\\Configuraton\\inline_service', 'RectorPrefix20220607\\Symfony\\Component\\DependencyInjection\\Loader\\Configuraton\\ref' => 'RectorPrefix20220607\\Symfony\\Component\\DependencyInjection\\Loader\\Configuraton\\service']);
    // https://github.com/symfony/symfony/pull/35308
    $rectorConfig->ruleWithConfiguration(NewArgToMethodCallRector::class, [new NewArgToMethodCall('RectorPrefix20220607\\Symfony\\Component\\Dotenv\\Dotenv', \true, 'usePutenv')]);
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [new RenameClassAndConstFetch('RectorPrefix20220607\\Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer', 'ROUND_FLOOR', 'NumberFormatter', 'ROUND_FLOOR'), new RenameClassAndConstFetch('RectorPrefix20220607\\Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer', 'ROUND_DOWN', 'NumberFormatter', 'ROUND_DOWN'), new RenameClassAndConstFetch('RectorPrefix20220607\\Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer', 'ROUND_HALF_DOWN', 'NumberFormatter', 'ROUND_HALFDOWN'), new RenameClassAndConstFetch('RectorPrefix20220607\\Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer', 'ROUND_HALF_EVEN', 'NumberFormatter', 'ROUND_HALFEVEN'), new RenameClassAndConstFetch('RectorPrefix20220607\\Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer', 'ROUND_HALFUP', 'NumberFormatter', 'ROUND_HALFUP'), new RenameClassAndConstFetch('RectorPrefix20220607\\Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer', 'ROUND_UP', 'NumberFormatter', 'ROUND_UP'), new RenameClassAndConstFetch('RectorPrefix20220607\\Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer', 'ROUND_CEILING', 'NumberFormatter', 'ROUND_CEILING')]);
    // see https://github.com/symfony/symfony/pull/36943
    $rectorConfig->ruleWithConfiguration(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('RectorPrefix20220607\\Symfony\\Bundle\\FrameworkBundle\\Kernel\\MicroKernelTrait', 'configureRoutes', 0, new ObjectType('RectorPrefix20220607\\Symfony\\Component\\Routing\\Loader\\Configurator\\RoutingConfigurator'))]);
    $rectorConfig->ruleWithConfiguration(StaticCallToNewRector::class, [new StaticCallToNew('RectorPrefix20220607\\Symfony\\Component\\HttpFoundation\\Response', 'create'), new StaticCallToNew('RectorPrefix20220607\\Symfony\\Component\\HttpFoundation\\JsonResponse', 'create'), new StaticCallToNew('RectorPrefix20220607\\Symfony\\Component\\HttpFoundation\\RedirectResponse', 'create'), new StaticCallToNew('RectorPrefix20220607\\Symfony\\Component\\HttpFoundation\\StreamedResponse', 'create')]);
    $rectorConfig->ruleWithConfiguration(RenameStringRector::class, [
        // @see https://github.com/symfony/symfony/pull/35858
        'ROLE_PREVIOUS_ADMIN' => 'IS_IMPERSONATOR',
    ]);
    $rectorConfig->rule(RouteCollectionBuilderToRoutingConfiguratorRector::class);
};
