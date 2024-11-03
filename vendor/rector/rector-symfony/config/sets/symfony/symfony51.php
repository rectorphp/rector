<?php

declare (strict_types=1);
namespace RectorPrefix202411;

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
use Rector\Symfony\Symfony51\Rector\Class_\LogoutHandlerToLogoutEventSubscriberRector;
use Rector\Symfony\Symfony51\Rector\Class_\LogoutSuccessHandlerToLogoutEventSubscriberRector;
use Rector\Symfony\Symfony51\Rector\ClassMethod\CommandConstantReturnCodeRector;
use Rector\Symfony\Symfony51\Rector\ClassMethod\RouteCollectionBuilderToRoutingConfiguratorRector;
use Rector\Transform\Rector\StaticCall\StaticCallToNewRector;
use Rector\Transform\ValueObject\StaticCallToNew;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        // @see https://github.com/symfony/symfony/pull/36243
        LogoutHandlerToLogoutEventSubscriberRector::class,
        LogoutSuccessHandlerToLogoutEventSubscriberRector::class,
        // @see https://symfony.com/blog/new-in-symfony-5-1-misc-improvements-part-1#added-constants-for-command-exit-codes
        CommandConstantReturnCodeRector::class,
        RouteCollectionBuilderToRoutingConfiguratorRector::class,
    ]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        'Symfony\\Component\\EventDispatcher\\LegacyEventDispatcherProxy' => 'Symfony\\Component\\EventDispatcher\\EventDispatcherInterface',
        'Symfony\\Component\\Form\\Extension\\Validator\\Util\\ServerParams' => 'Symfony\\Component\\Form\\Util\\ServerParams',
        // @see https://github.com/symfony/symfony/pull/35092
        'Symfony\\Component\\Inflector' => 'Symfony\\Component\\String\\Inflector\\InflectorInterface',
    ]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        new MethodCallRename('Symfony\\Component\\Config\\Definition\\BaseNode', 'getDeprecationMessage', 'getDeprecation'),
        new MethodCallRename('Symfony\\Component\\DependencyInjection\\Definition', 'getDeprecationMessage', 'getDeprecation'),
        new MethodCallRename('Symfony\\Component\\DependencyInjection\\Alias', 'getDeprecationMessage', 'getDeprecation'),
        // @see https://github.com/symfony/symfony/pull/35828
        new MethodCallRename('Symfony\\Component\\Notifier\\Bridge\\Slack\\Slack', 'channel', 'recipient'),
    ]);
    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, ['Symfony\\Component\\DependencyInjection\\Loader\\Configuraton\\inline' => 'Symfony\\Component\\DependencyInjection\\Loader\\Configuraton\\inline_service', 'Symfony\\Component\\DependencyInjection\\Loader\\Configuraton\\ref' => 'Symfony\\Component\\DependencyInjection\\Loader\\Configuraton\\service']);
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [new RenameClassAndConstFetch('Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer', 'ROUND_FLOOR', 'NumberFormatter', 'ROUND_FLOOR'), new RenameClassAndConstFetch('Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer', 'ROUND_DOWN', 'NumberFormatter', 'ROUND_DOWN'), new RenameClassAndConstFetch('Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer', 'ROUND_HALF_DOWN', 'NumberFormatter', 'ROUND_HALFDOWN'), new RenameClassAndConstFetch('Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer', 'ROUND_HALF_EVEN', 'NumberFormatter', 'ROUND_HALFEVEN'), new RenameClassAndConstFetch('Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer', 'ROUND_HALFUP', 'NumberFormatter', 'ROUND_HALFUP'), new RenameClassAndConstFetch('Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer', 'ROUND_UP', 'NumberFormatter', 'ROUND_UP'), new RenameClassAndConstFetch('Symfony\\Component\\Form\\Extension\\Core\\DataTransformer\\NumberToLocalizedStringTransformer', 'ROUND_CEILING', 'NumberFormatter', 'ROUND_CEILING')]);
    // @see https://github.com/symfony/symfony/pull/36943
    $rectorConfig->ruleWithConfiguration(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('Symfony\\Bundle\\FrameworkBundle\\Kernel\\MicroKernelTrait', 'configureRoutes', 0, new ObjectType('Symfony\\Component\\Routing\\Loader\\Configurator\\RoutingConfigurator'))]);
    $rectorConfig->ruleWithConfiguration(StaticCallToNewRector::class, [new StaticCallToNew('Symfony\\Component\\HttpFoundation\\Response', 'create'), new StaticCallToNew('Symfony\\Component\\HttpFoundation\\JsonResponse', 'create'), new StaticCallToNew('Symfony\\Component\\HttpFoundation\\RedirectResponse', 'create'), new StaticCallToNew('Symfony\\Component\\HttpFoundation\\StreamedResponse', 'create')]);
    $rectorConfig->ruleWithConfiguration(RenameStringRector::class, [
        // @see https://github.com/symfony/symfony/pull/35858
        'ROLE_PREVIOUS_ADMIN' => 'IS_IMPERSONATOR',
    ]);
};
