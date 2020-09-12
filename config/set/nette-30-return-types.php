<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\Generic\ValueObject\AddReturnTypeDeclaration;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    # scalar type hints, see https://github.com/nette/security/commit/84024f612fb3f55f5d6e3e3e28eef1ad0388fa56
    $services->set(AddReturnTypeDeclarationRector::class)
        ->call('configure', [[
            AddReturnTypeDeclarationRector::METHOD_RETURN_TYPES => inline_value_objects([
                new AddReturnTypeDeclaration('Nette\Mail\Mailer', 'send', 'void'),
                new AddReturnTypeDeclaration(
                    'Nette\Forms\Rendering\DefaultFormRenderer',
                    'renderControl',
                    'Nette\Utils\Html'
                ),
                new AddReturnTypeDeclaration('Nette\Caching\Cache', 'generateKey', 'string'),
                new AddReturnTypeDeclaration('Nette\Security\IResource', 'getResourceId', 'string'),
                new AddReturnTypeDeclaration(
                    'Nette\Security\IAuthenticator',
                    'authenticate',
                    'Nette\Security\IIdentity'
                ),
                new AddReturnTypeDeclaration('Nette\Security\IAuthorizator', 'isAllowed', 'bool'),
                new AddReturnTypeDeclaration('Nette\Security\Identity', 'getData', 'array'),
                new AddReturnTypeDeclaration('Nette\Security\IIdentity', 'getRoles', 'array'),
                new AddReturnTypeDeclaration('Nette\Security\User', 'getStorage', 'Nette\Security\IUserStorage'),
                new AddReturnTypeDeclaration('Nette\Security\User', 'login', 'void'),
                new AddReturnTypeDeclaration('Nette\Security\User', 'logout', 'void'),
                new AddReturnTypeDeclaration('Nette\Security\User', 'isLoggedIn', 'bool'),
                new AddReturnTypeDeclaration('Nette\Security\User', 'getIdentity', '?Nette\Security\IIdentity'),
                new AddReturnTypeDeclaration(
                    'Nette\Security\User',
                    'getAuthenticator',
                    '?Nette\Security\IAuthenticator'
                ),
                new AddReturnTypeDeclaration('Nette\Security\User', 'getAuthorizator', '?Nette\Security\IAuthorizator'),
                new AddReturnTypeDeclaration('Nette\Security\User', 'getLogoutReason', '?int'),
                new AddReturnTypeDeclaration('Nette\Security\User', 'getRoles', 'array'),
                new AddReturnTypeDeclaration('Nette\Security\User', 'isInRole', 'bool'),
                new AddReturnTypeDeclaration('Nette\Security\User', 'isAllowed', 'bool'),
                new AddReturnTypeDeclaration('Nette\Security\IUserStorage', 'isAuthenticated', 'bool'),
                new AddReturnTypeDeclaration('Nette\Security\IUserStorage', 'getIdentity', '?Nette\Security\IIdentity'),
                new AddReturnTypeDeclaration('Nette\Security\IUserStorage', 'getLogoutReason', '?int'),
                new AddReturnTypeDeclaration(
                    'Nette\ComponentModel\Component',
                    'lookup',
                    'Nette\ComponentModel\IComponent'
                ),
                new AddReturnTypeDeclaration('Nette\ComponentModel\Component', 'lookupPath', '?string'),
                new AddReturnTypeDeclaration('Nette\ComponentModel\Component', 'monitor', 'void'),
                new AddReturnTypeDeclaration('Nette\ComponentModel\Component', 'unmonitor', 'void'),
                new AddReturnTypeDeclaration('Nette\ComponentModel\Component', 'attached', 'void'),
                new AddReturnTypeDeclaration('Nette\ComponentModel\Component', 'detached', 'void'),
                new AddReturnTypeDeclaration('Nette\ComponentModel\Component', 'getName', '?string'),
                new AddReturnTypeDeclaration('Nette\ComponentModel\IComponent', 'getName', '?string'),
                new AddReturnTypeDeclaration(
                    'Nette\ComponentModel\IComponent',
                    'getParent',
                    '?Nette\ComponentModel\IContainer'
                ),
                new AddReturnTypeDeclaration('Nette\ComponentModel\Container', 'removeComponent', 'void'),
                new AddReturnTypeDeclaration(
                    'Nette\ComponentModel\Container',
                    'getComponent',
                    '?Nette\ComponentModel\IComponent'
                ),
                new AddReturnTypeDeclaration(
                    'Nette\ComponentModel\Container',
                    'createComponent',
                    '?Nette\ComponentModel\IComponent'
                ),
                new AddReturnTypeDeclaration('Nette\ComponentModel\Container', 'getComponents', 'Iterator'),
                new AddReturnTypeDeclaration('Nette\ComponentModel\Container', 'validateChildComponent', 'void'),
                new AddReturnTypeDeclaration(
                    'Nette\ComponentModel\Container',
                    '_isCloning',
                    '?Nette\ComponentModel\IComponent'
                ),
                new AddReturnTypeDeclaration('Nette\ComponentModel\IContainer', 'removeComponent', 'void'),
                new AddReturnTypeDeclaration(
                    'Nette\ComponentModel\IContainer',
                    'getComponent',
                    '?Nette\ComponentModel\IContainer'
                ),
                new AddReturnTypeDeclaration('Nette\ComponentModel\IContainer', 'getComponents', 'Iterator'),
                new AddReturnTypeDeclaration('Nette\Application\Application', 'run', 'void'),
                new AddReturnTypeDeclaration(
                    'Nette\Application\Application',
                    'createInitialRequest',
                    'Nette\Application\Request'
                ),
                new AddReturnTypeDeclaration('Nette\Application\Application', 'processRequest', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\Application', 'processException', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\Application', 'getRequests', 'array'),
                new AddReturnTypeDeclaration(
                    'Nette\Application\Application',
                    'getPresenter',
                    '?Nette\Application\IPresenter'
                ),
                new AddReturnTypeDeclaration(
                    'Nette\Application\Application',
                    'getRouter',
                    '?Nette\Application\IRouter'
                ),
                new AddReturnTypeDeclaration(
                    'Nette\Application\Application',
                    'getPresenterFactory',
                    '?Nette\Application\IPresenterFactory'
                ),
                new AddReturnTypeDeclaration('Nette\Application\Helpers', 'splitName', 'array'),
                new AddReturnTypeDeclaration('Nette\Application\IPresenter', 'run', 'Nette\Application\IResponse'),
                new AddReturnTypeDeclaration('Nette\Application\IPresenterFactory', 'getPresenterClass', 'string'),
                new AddReturnTypeDeclaration(
                    'Nette\Application\IPresenterFactory',
                    'createPresenter',
                    'Nette\Application\IPresenter'
                ),
                new AddReturnTypeDeclaration('Nette\Application\PresenterFactory', 'formatPresenterClass', 'string'),
                new AddReturnTypeDeclaration('Nette\Application\PresenterFactory', 'unformatPresenterClass', '?string'),
                new AddReturnTypeDeclaration('Nette\Application\IResponse', 'send', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\Responses\FileResponse', 'getFile', 'string'),
                new AddReturnTypeDeclaration('Nette\Application\Responses\FileResponse', 'getName', 'string'),
                new AddReturnTypeDeclaration('Nette\Application\Responses\FileResponse', 'getContentType', 'string'),
                new AddReturnTypeDeclaration(
                    'Nette\Application\Responses\ForwardResponse',
                    'getRequest',
                    'Nette\Application\Request'
                ),
                new AddReturnTypeDeclaration('Nette\Application\Request', 'getPresenterName', 'string'),
                new AddReturnTypeDeclaration('Nette\Application\Request', 'getParameters', 'array'),
                new AddReturnTypeDeclaration('Nette\Application\Request', 'getFiles', 'array'),
                new AddReturnTypeDeclaration('Nette\Application\Request', 'getMethod', '?string'),
                new AddReturnTypeDeclaration('Nette\Application\Request', 'isMethod', 'bool'),
                new AddReturnTypeDeclaration('Nette\Application\Request', 'hasFlag', 'bool'),
                new AddReturnTypeDeclaration('Nette\Application\RedirectResponse', 'getUrl', 'string'),
                new AddReturnTypeDeclaration('Nette\Application\RedirectResponse', 'getCode', 'int'),
                new AddReturnTypeDeclaration('Nette\Application\JsonResponse', 'getContentType', 'string'),
                new AddReturnTypeDeclaration('Nette\Application\IRouter', 'match', '?Nette\Application\Request'),
                new AddReturnTypeDeclaration('Nette\Application\IRouter', 'constructUrl', '?string'),
                new AddReturnTypeDeclaration('Nette\Application\Routers\Route', 'getMask', 'string'),
                new AddReturnTypeDeclaration('Nette\Application\Routers\Route', 'getDefaults', 'array'),
                new AddReturnTypeDeclaration('Nette\Application\Routers\Route', 'getFlags', 'int'),
                new AddReturnTypeDeclaration('Nette\Application\Routers\Route', 'getTargetPresenters', '?array'),
                new AddReturnTypeDeclaration('Nette\Application\Routers\RouteList', 'warmupCache', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\Routers\RouteList', 'offsetSet', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\Routers\RouteList', 'getModule', '?string'),
                new AddReturnTypeDeclaration('Nette\Application\Routers\CliRouter', 'getDefaults', 'array'),
                new AddReturnTypeDeclaration(
                    'Nette\Application\UI\Component',
                    'getPresenter',
                    '?Nette\Application\UI\Presenter'
                ),
                new AddReturnTypeDeclaration('Nette\Application\UI\Component', 'getUniqueId', 'string'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Component', 'tryCall', 'bool'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Component', 'checkRequirements', 'void'),
                new AddReturnTypeDeclaration(
                    'Nette\Application\UI\Component',
                    'getReflection',
                    'Nette\Application\UI\ComponentReflection'
                ),
                new AddReturnTypeDeclaration('Nette\Application\UI\Component', 'loadState', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Component', 'saveState', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Component', 'getParameters', 'array'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Component', 'getParameterId', 'string'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Component', 'getPersistentParams', 'array'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Component', 'signalReceived', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Component', 'formatSignalMethod', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Component', 'link', 'string'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Component', 'lazyLink', 'Nette\Application\UI\Link'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Component', 'isLinkCurrent', 'bool'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Component', 'redirect', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Component', 'redirectPermanent', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Component', 'offsetSet', 'void'),
                new AddReturnTypeDeclaration(
                    'Nette\Application\UI\Component',
                    'offsetGet',
                    'Nette\ComponentModel\IComponent'
                ),
                new AddReturnTypeDeclaration('Nette\Application\UI\Component', 'offsetExists', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Component', 'offsetUnset', 'void'),
                new AddReturnTypeDeclaration(
                    'Nette\Application\UI\Presenter',
                    'getRequest',
                    'Nette\Application\Request'
                ),
                new AddReturnTypeDeclaration(
                    'Nette\Application\UI\Presenter',
                    'getPresenter',
                    'Nette\Application\UI\Presenter'
                ),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'getUniqueId', 'string'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'checkRequirements', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'processSignal', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'getSignal', '?array'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'isSignalReceiver', 'bool'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'getAction', 'string'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'changeAction', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'getView', 'string'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'sendTemplate', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'findLayoutTemplateFile', '?string'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'formatLayoutTemplateFiles', 'array'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'formatTemplateFiles', 'array'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'formatActionMethod', 'string'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'formatRenderMethod', 'string'),
                new AddReturnTypeDeclaration(
                    'Nette\Application\UI\Presenter',
                    'createTemplate',
                    'Nette\Application\UI\ITemplate'
                ),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'getPayload', 'stdClass'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'isAjax', 'bool'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'sendPayload', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'sendJson', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'sendResponse', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'terminate', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'forward', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'redirectUrl', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'error', 'void'),
                new AddReturnTypeDeclaration(
                    'Nette\Application\UI\Presenter',
                    'getLastCreatedRequest',
                    '?Nette\Application\Request'
                ),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'getLastCreatedRequestFlag', 'bool'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'canonicalize', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'lastModified', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'createRequest', '?string'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'argsToParams', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'handleInvalidLink', 'string'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'storeRequest', 'string'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'restoreRequest', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'getPersistentComponents', 'array'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'getGlobalState', 'array'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'saveGlobalState', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'initGlobalParameters', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'popGlobalParameters', 'array'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'getFlashKey', '?string'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'hasFlashSession', 'bool'),
                new AddReturnTypeDeclaration(
                    'Nette\Application\UI\Presenter',
                    'getFlashSession',
                    'Nette\Http\SessionSection'
                ),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'getContext', 'Nette\DI\Container'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'getHttpRequest', 'Nette\Http\IRequest'),
                new AddReturnTypeDeclaration(
                    'Nette\Application\UI\Presenter',
                    'getHttpResponse',
                    'Nette\Http\IResponse'
                ),
                new AddReturnTypeDeclaration('Nette\Application\UI\Presenter', 'getUser', 'Nette\Security\User'),
                new AddReturnTypeDeclaration(
                    'Nette\Application\UI\Presenter',
                    'getTemplateFactory',
                    'Nette\Application\UI\ITemplateFactory'
                ),
                new AddReturnTypeDeclaration('Nette\Application\Exception\BadRequestException', 'getHttpCode', 'int'),
                new AddReturnTypeDeclaration('Nette\Bridges\ApplicationDI\LatteExtension', 'addMacro', 'void'),
                new AddReturnTypeDeclaration(
                    'Nette\Bridges\ApplicationDI\PresenterFactoryCallback',
                    '__invoke',
                    'Nette\Application\IPresenter'
                ),
                new AddReturnTypeDeclaration('Nette\Bridges\ApplicationLatte\ILatteFactory', 'create', 'Latte\Engine'),
                new AddReturnTypeDeclaration('Nette\Bridges\ApplicationLatte\Template', 'getLatte', 'Latte\Engine'),
                new AddReturnTypeDeclaration('Nette\Bridges\ApplicationLatte\Template', 'render', 'void'),
                new AddReturnTypeDeclaration('Nette\Bridges\ApplicationLatte\Template', '__toString', 'string'),
                new AddReturnTypeDeclaration('Nette\Bridges\ApplicationLatte\Template', 'getFile', '?string'),
                new AddReturnTypeDeclaration('Nette\Bridges\ApplicationLatte\Template', 'getParameters', 'array'),
                new AddReturnTypeDeclaration('Nette\Bridges\ApplicationLatte\Template', '__set', 'void'),
                new AddReturnTypeDeclaration('Nette\Bridges\ApplicationLatte\Template', '__unset', 'void'),
                new AddReturnTypeDeclaration(
                    'Nette\Bridges\ApplicationLatte\TemplateFactory',
                    'createTemplate',
                    'Nette\Application\UI\ITemplate'
                ),
                new AddReturnTypeDeclaration('Nette\Bridges\ApplicationLatte\UIMacros', 'initialize', 'void'),
                new AddReturnTypeDeclaration('Nette\Bridges\ApplicationTracy\RoutingPanel', 'initializePanel', 'void'),
                new AddReturnTypeDeclaration('Nette\Bridges\ApplicationTracy\RoutingPanel', 'getTab', 'string'),
                new AddReturnTypeDeclaration('Nette\Bridges\ApplicationTracy\RoutingPanel', 'getPanel', 'string'),
                new AddReturnTypeDeclaration('Nette\Bridges\ApplicationLatte\UIRuntime', 'initialize', 'void'),
                new AddReturnTypeDeclaration(
                    'Nette\Application\UI\ComponentReflection',
                    'getPersistentParams',
                    'array'
                ),
                new AddReturnTypeDeclaration(
                    'Nette\Application\UI\ComponentReflection',
                    'getPersistentComponents',
                    'array'
                ),
                new AddReturnTypeDeclaration('Nette\Application\UI\ComponentReflection', 'hasCallableMethod', 'bool'),
                new AddReturnTypeDeclaration('Nette\Application\UI\ComponentReflection', 'combineArgs', 'array'),
                new AddReturnTypeDeclaration('Nette\Application\UI\ComponentReflection', 'convertType', 'bool'),
                new AddReturnTypeDeclaration('Nette\Application\UI\ComponentReflection', 'parseAnnotation', '?array'),
                new AddReturnTypeDeclaration('Nette\Application\UI\ComponentReflection', 'getParameterType', 'array'),
                new AddReturnTypeDeclaration('Nette\Application\UI\ComponentReflection', 'hasAnnotation', 'bool'),
                new AddReturnTypeDeclaration('Nette\Application\UI\ComponentReflection', 'getMethods', 'array'),
                new AddReturnTypeDeclaration(
                    'Nette\Application\UI\Control',
                    'getTemplate',
                    'Nette\Application\UI\ITemplate'
                ),
                new AddReturnTypeDeclaration(
                    'Nette\Application\UI\Control',
                    'createTemplate',
                    'Nette\Application\UI\ITemplate'
                ),
                new AddReturnTypeDeclaration('Nette\Application\UI\Control', 'templatePrepareFilters', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Control', 'flashMessage', 'stdClass'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Control', 'redrawControl', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Control', 'isControlInvalid', 'bool'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Control', 'getSnippetId', 'string'),
                new AddReturnTypeDeclaration(
                    'Nette\Application\UI\Form',
                    'getPresenter',
                    '?Nette\Application\UI\Presenter'
                ),
                new AddReturnTypeDeclaration('Nette\Application\UI\Form', 'signalReceived', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\IRenderable', 'redrawControl', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\IRenderable', 'isControlInvalid', 'bool'),
                new AddReturnTypeDeclaration('Nette\Application\UI\ITemplate', 'render', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\ITemplate', 'getFile', '?string'),
                new AddReturnTypeDeclaration(
                    'Nette\Application\UI\ITemplateFactory',
                    'createTemplate',
                    'Nette\Application\UI\ITemplate'
                ),
                new AddReturnTypeDeclaration('Nette\Application\UI\Link', 'getDestination', 'string'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Link', 'getParameters', 'array'),
                new AddReturnTypeDeclaration('Nette\Application\UI\Link', '__toString', 'string'),
                new AddReturnTypeDeclaration('Nette\Application\UI\MethodReflection', 'hasAnnotation', 'bool'),
                new AddReturnTypeDeclaration('Nette\Application\UI\IStatePersistent', 'loadState', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\IStatePersistent', 'saveState', 'void'),
                new AddReturnTypeDeclaration('Nette\Application\UI\ISignalReceiver', 'signalReceived', 'void'),
                new AddReturnTypeDeclaration(
                    'Nette\Application\Routers\SimpleRouter',
                    'match',
                    '?Nette\Application\Request'
                ),
                new AddReturnTypeDeclaration('Nette\Application\Routers\SimpleRouter', 'getDefaults', 'array'),
                new AddReturnTypeDeclaration('Nette\Application\Routers\SimpleRouter', 'getFlags', 'int'),
                new AddReturnTypeDeclaration('Nette\Application\LinkGenerator', 'link', 'string'),
                new AddReturnTypeDeclaration('Nette\Application\MicroPresenter', 'getContext', '?Nette\DI\Container'),
                new AddReturnTypeDeclaration(
                    'Nette\Application\MicroPresenter',
                    'createTemplate',
                    'Nette\Application\UI\ITemplate'
                ),
                new AddReturnTypeDeclaration(
                    'Nette\Application\MicroPresenter',
                    'redirectUrl',
                    'Nette\Application\Responses\RedirectResponse'
                ),
                new AddReturnTypeDeclaration('Nette\Application\MicroPresenter', 'error', 'void'),
                new AddReturnTypeDeclaration(
                    'Nette\Application\MicroPresenter',
                    'getRequest',
                    '?Nette\Application\Request'
                ),
            ]),
        ]]);
};
