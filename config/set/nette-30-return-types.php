<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\Generic\ValueObject\MethodReturnType;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    # scalar type hints, see https://github.com/nette/security/commit/84024f612fb3f55f5d6e3e3e28eef1ad0388fa56
    $services->set(AddReturnTypeDeclarationRector::class)
        ->call('configure', [[
            AddReturnTypeDeclarationRector::METHOD_RETURN_TYPES => inline_value_objects(
                [
                    new MethodReturnType('Nette\Mail\Mailer', 'send', 'void'),
                    new MethodReturnType(
                        'Nette\Forms\Rendering\DefaultFormRenderer',
                        'renderControl',
                        'Nette\Utils\Html'
                    ),
                    new MethodReturnType('Nette\Caching\Cache', 'generateKey', 'string'),
                    new MethodReturnType('Nette\Security\IResource', 'getResourceId', 'string'),
                    new MethodReturnType('Nette\Security\IAuthenticator', 'authenticate', 'Nette\Security\IIdentity'),
                    new MethodReturnType('Nette\Security\IAuthorizator', 'isAllowed', 'bool'),
                    new MethodReturnType('Nette\Security\Identity', 'getData', 'array'),
                    new MethodReturnType('Nette\Security\IIdentity', 'getRoles', 'array'),
                    new MethodReturnType('Nette\Security\User', 'getStorage', 'Nette\Security\IUserStorage'),
                    new MethodReturnType('Nette\Security\User', 'login', 'void'),
                    new MethodReturnType('Nette\Security\User', 'logout', 'void'),
                    new MethodReturnType('Nette\Security\User', 'isLoggedIn', 'bool'),
                    new MethodReturnType('Nette\Security\User', 'getIdentity', '?Nette\Security\IIdentity'),
                    new MethodReturnType('Nette\Security\User', 'getAuthenticator', '?Nette\Security\IAuthenticator'),
                    new MethodReturnType('Nette\Security\User', 'getAuthorizator', '?Nette\Security\IAuthorizator'),
                    new MethodReturnType('Nette\Security\User', 'getLogoutReason', '?int'),
                    new MethodReturnType('Nette\Security\User', 'getRoles', 'array'),
                    new MethodReturnType('Nette\Security\User', 'isInRole', 'bool'),
                    new MethodReturnType('Nette\Security\User', 'isAllowed', 'bool'),
                    new MethodReturnType('Nette\Security\IUserStorage', 'isAuthenticated', 'bool'),
                    new MethodReturnType('Nette\Security\IUserStorage', 'getIdentity', '?Nette\Security\IIdentity'),
                    new MethodReturnType('Nette\Security\IUserStorage', 'getLogoutReason', '?int'),
                    new MethodReturnType('Nette\ComponentModel\Component', 'lookup', 'Nette\ComponentModel\IComponent'),
                    new MethodReturnType('Nette\ComponentModel\Component', 'lookupPath', '?string'),
                    new MethodReturnType('Nette\ComponentModel\Component', 'monitor', 'void'),
                    new MethodReturnType('Nette\ComponentModel\Component', 'unmonitor', 'void'),
                    new MethodReturnType('Nette\ComponentModel\Component', 'attached', 'void'),
                    new MethodReturnType('Nette\ComponentModel\Component', 'detached', 'void'),
                    new MethodReturnType('Nette\ComponentModel\Component', 'getName', '?string'),
                    new MethodReturnType('Nette\ComponentModel\IComponent', 'getName', '?string'),
                    new MethodReturnType(
                        'Nette\ComponentModel\IComponent',
                        'getParent',
                        '?Nette\ComponentModel\IContainer'
                    ),
                    new MethodReturnType('Nette\ComponentModel\Container', 'removeComponent', 'void'),
                    new MethodReturnType(
                        'Nette\ComponentModel\Container',
                        'getComponent',
                        '?Nette\ComponentModel\IComponent'
                    ),
                    new MethodReturnType(
                        'Nette\ComponentModel\Container',
                        'createComponent',
                        '?Nette\ComponentModel\IComponent'
                    ),
                    new MethodReturnType('Nette\ComponentModel\Container', 'getComponents', 'Iterator'),
                    new MethodReturnType('Nette\ComponentModel\Container', 'validateChildComponent', 'void'),
                    new MethodReturnType(
                        'Nette\ComponentModel\Container',
                        '_isCloning',
                        '?Nette\ComponentModel\IComponent'
                    ),
                    new MethodReturnType('Nette\ComponentModel\IContainer', 'removeComponent', 'void'),
                    new MethodReturnType(
                        'Nette\ComponentModel\IContainer',
                        'getComponent',
                        '?Nette\ComponentModel\IContainer'
                    ),
                    new MethodReturnType('Nette\ComponentModel\IContainer', 'getComponents', 'Iterator'),
                    new MethodReturnType('Nette\Application\Application', 'run', 'void'),
                    new MethodReturnType(
                        'Nette\Application\Application',
                        'createInitialRequest',
                        'Nette\Application\Request'
                    ),
                    new MethodReturnType('Nette\Application\Application', 'processRequest', 'void'),
                    new MethodReturnType('Nette\Application\Application', 'processException', 'void'),
                    new MethodReturnType('Nette\Application\Application', 'getRequests', 'array'),
                    new MethodReturnType(
                        'Nette\Application\Application',
                        'getPresenter',
                        '?Nette\Application\IPresenter'
                    ),
                    new MethodReturnType('Nette\Application\Application', 'getRouter', '?Nette\Application\IRouter'),
                    new MethodReturnType(
                                                            'Nette\Application\Application',
                                                            'getPresenterFactory',
                                                            '?Nette\Application\IPresenterFactory'
                                                        ),
                    new MethodReturnType('Nette\Application\Helpers', 'splitName', 'array'),
                    new MethodReturnType('Nette\Application\IPresenter', 'run', 'Nette\Application\IResponse'),
                    new MethodReturnType('Nette\Application\IPresenterFactory', 'getPresenterClass', 'string'),
                    new MethodReturnType(
                        'Nette\Application\IPresenterFactory',
                        'createPresenter',
                        'Nette\Application\IPresenter'
                    ),
                    new MethodReturnType('Nette\Application\PresenterFactory', 'formatPresenterClass', 'string'),
                    new MethodReturnType('Nette\Application\PresenterFactory', 'unformatPresenterClass', '?string'),
                    new MethodReturnType('Nette\Application\IResponse', 'send', 'void'),
                    new MethodReturnType('Nette\Application\Responses\FileResponse', 'getFile', 'string'),
                    new MethodReturnType('Nette\Application\Responses\FileResponse', 'getName', 'string'),
                    new MethodReturnType('Nette\Application\Responses\FileResponse', 'getContentType', 'string'),
                    new MethodReturnType(
                        'Nette\Application\Responses\ForwardResponse',
                        'getRequest',
                        'Nette\Application\Request'
                    ),
                    new MethodReturnType('Nette\Application\Request', 'getPresenterName', 'string'),
                    new MethodReturnType('Nette\Application\Request', 'getParameters', 'array'),
                    new MethodReturnType('Nette\Application\Request', 'getFiles', 'array'),
                    new MethodReturnType('Nette\Application\Request', 'getMethod', '?string'),
                    new MethodReturnType('Nette\Application\Request', 'isMethod', 'bool'),
                    new MethodReturnType('Nette\Application\Request', 'hasFlag', 'bool'),
                    new MethodReturnType('Nette\Application\RedirectResponse', 'getUrl', 'string'),
                    new MethodReturnType('Nette\Application\RedirectResponse', 'getCode', 'int'),
                    new MethodReturnType('Nette\Application\JsonResponse', 'getContentType', 'string'),
                    new MethodReturnType('Nette\Application\IRouter', 'match', '?Nette\Application\Request'),
                    new MethodReturnType('Nette\Application\IRouter', 'constructUrl', '?string'),
                    new MethodReturnType('Nette\Application\Routers\Route', 'getMask', 'string'),
                    new MethodReturnType('Nette\Application\Routers\Route', 'getDefaults', 'array'),
                    new MethodReturnType('Nette\Application\Routers\Route', 'getFlags', 'int'),
                    new MethodReturnType('Nette\Application\Routers\Route', 'getTargetPresenters', '?array'),
                    new MethodReturnType('Nette\Application\Routers\RouteList', 'warmupCache', 'void'),
                    new MethodReturnType('Nette\Application\Routers\RouteList', 'offsetSet', 'void'),
                    new MethodReturnType('Nette\Application\Routers\RouteList', 'getModule', '?string'),
                    new MethodReturnType('Nette\Application\Routers\CliRouter', 'getDefaults', 'array'),
                    new MethodReturnType(
                        'Nette\Application\UI\Component',
                        'getPresenter',
                        '?Nette\Application\UI\Presenter'
                    ),
                    new MethodReturnType('Nette\Application\UI\Component', 'getUniqueId', 'string'),
                    new MethodReturnType('Nette\Application\UI\Component', 'tryCall', 'bool'),
                    new MethodReturnType('Nette\Application\UI\Component', 'checkRequirements', 'void'),
                    new MethodReturnType(
                        'Nette\Application\UI\Component',
                        'getReflection',
                        'Nette\Application\UI\ComponentReflection'
                    ),
                    new MethodReturnType('Nette\Application\UI\Component', 'loadState', 'void'),
                    new MethodReturnType('Nette\Application\UI\Component', 'saveState', 'void'),
                    new MethodReturnType('Nette\Application\UI\Component', 'getParameters', 'array'),
                    new MethodReturnType('Nette\Application\UI\Component', 'getParameterId', 'string'),
                    new MethodReturnType('Nette\Application\UI\Component', 'getPersistentParams', 'array'),
                    new MethodReturnType('Nette\Application\UI\Component', 'signalReceived', 'void'),
                    new MethodReturnType('Nette\Application\UI\Component', 'formatSignalMethod', 'void'),
                    new MethodReturnType('Nette\Application\UI\Component', 'link', 'string'),
                    new MethodReturnType('Nette\Application\UI\Component', 'lazyLink', 'Nette\Application\UI\Link'),
                    new MethodReturnType('Nette\Application\UI\Component', 'isLinkCurrent', 'bool'),
                    new MethodReturnType('Nette\Application\UI\Component', 'redirect', 'void'),
                    new MethodReturnType('Nette\Application\UI\Component', 'redirectPermanent', 'void'),
                    new MethodReturnType('Nette\Application\UI\Component', 'offsetSet', 'void'),
                    new MethodReturnType(
                        'Nette\Application\UI\Component',
                        'offsetGet',
                        'Nette\ComponentModel\IComponent'
                    ),
                    new MethodReturnType('Nette\Application\UI\Component', 'offsetExists', 'void'),
                    new MethodReturnType('Nette\Application\UI\Component', 'offsetUnset', 'void'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'getRequest', 'Nette\Application\Request'),
                    new MethodReturnType(
                        'Nette\Application\UI\Presenter',
                        'getPresenter',
                        'Nette\Application\UI\Presenter'
                    ),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'getUniqueId', 'string'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'checkRequirements', 'void'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'processSignal', 'void'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'getSignal', '?array'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'isSignalReceiver', 'bool'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'getAction', 'string'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'changeAction', 'void'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'getView', 'string'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'sendTemplate', 'void'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'findLayoutTemplateFile', '?string'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'formatLayoutTemplateFiles', 'array'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'formatTemplateFiles', 'array'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'formatActionMethod', 'string'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'formatRenderMethod', 'string'),
                    new MethodReturnType(
                        'Nette\Application\UI\Presenter',
                        'createTemplate',
                        'Nette\Application\UI\ITemplate'
                    ),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'getPayload', 'stdClass'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'isAjax', 'bool'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'sendPayload', 'void'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'sendJson', 'void'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'sendResponse', 'void'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'terminate', 'void'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'forward', 'void'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'redirectUrl', 'void'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'error', 'void'),
                    new MethodReturnType(
                        'Nette\Application\UI\Presenter',
                        'getLastCreatedRequest',
                        '?Nette\Application\Request'
                    ),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'getLastCreatedRequestFlag', 'bool'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'canonicalize', 'void'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'lastModified', 'void'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'createRequest', '?string'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'argsToParams', 'void'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'handleInvalidLink', 'string'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'storeRequest', 'string'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'restoreRequest', 'void'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'getPersistentComponents', 'array'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'getGlobalState', 'array'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'saveGlobalState', 'void'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'initGlobalParameters', 'void'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'popGlobalParameters', 'array'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'getFlashKey', '?string'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'hasFlashSession', 'bool'),
                    new MethodReturnType(
                        'Nette\Application\UI\Presenter',
                        'getFlashSession',
                        'Nette\Http\SessionSection'
                    ),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'getContext', 'Nette\DI\Container'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'getHttpRequest', 'Nette\Http\IRequest'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'getHttpResponse', 'Nette\Http\IResponse'),
                    new MethodReturnType('Nette\Application\UI\Presenter', 'getUser', 'Nette\Security\User'),
                    new MethodReturnType(
                                                            'Nette\Application\UI\Presenter',
                                                            'getTemplateFactory',
                                                            'Nette\Application\UI\ITemplateFactory'
                                                        ),
                    new MethodReturnType('Nette\Application\Exception\BadRequestException', 'getHttpCode', 'int'),
                    new MethodReturnType('Nette\Bridges\ApplicationDI\LatteExtension', 'addMacro', 'void'),
                    new MethodReturnType(
                                                            'Nette\Bridges\ApplicationDI\PresenterFactoryCallback',
                                                            '__invoke',
                                                            'Nette\Application\IPresenter'
                                                        ),
                    new MethodReturnType('Nette\Bridges\ApplicationLatte\ILatteFactory', 'create', 'Latte\Engine'),
                    new MethodReturnType('Nette\Bridges\ApplicationLatte\Template', 'getLatte', 'Latte\Engine'),
                    new MethodReturnType('Nette\Bridges\ApplicationLatte\Template', 'render', 'void'),
                    new MethodReturnType('Nette\Bridges\ApplicationLatte\Template', '__toString', 'string'),
                    new MethodReturnType('Nette\Bridges\ApplicationLatte\Template', 'getFile', '?string'),
                    new MethodReturnType('Nette\Bridges\ApplicationLatte\Template', 'getParameters', 'array'),
                    new MethodReturnType('Nette\Bridges\ApplicationLatte\Template', '__set', 'void'),
                    new MethodReturnType('Nette\Bridges\ApplicationLatte\Template', '__unset', 'void'),
                    new MethodReturnType(
                                                            'Nette\Bridges\ApplicationLatte\TemplateFactory',
                                                            'createTemplate',
                                                            'Nette\Application\UI\ITemplate'
                                                        ),
                    new MethodReturnType('Nette\Bridges\ApplicationLatte\UIMacros', 'initialize', 'void'),
                    new MethodReturnType('Nette\Bridges\ApplicationTracy\RoutingPanel', 'initializePanel', 'void'),
                    new MethodReturnType('Nette\Bridges\ApplicationTracy\RoutingPanel', 'getTab', 'string'),
                    new MethodReturnType('Nette\Bridges\ApplicationTracy\RoutingPanel', 'getPanel', 'string'),
                    new MethodReturnType('Nette\Bridges\ApplicationLatte\UIRuntime', 'initialize', 'void'),
                    new MethodReturnType('Nette\Application\UI\ComponentReflection', 'getPersistentParams', 'array'),
                    new MethodReturnType(
                        'Nette\Application\UI\ComponentReflection',
                        'getPersistentComponents',
                        'array'
                    ),
                    new MethodReturnType('Nette\Application\UI\ComponentReflection', 'hasCallableMethod', 'bool'),
                    new MethodReturnType('Nette\Application\UI\ComponentReflection', 'combineArgs', 'array'),
                    new MethodReturnType('Nette\Application\UI\ComponentReflection', 'convertType', 'bool'),
                    new MethodReturnType('Nette\Application\UI\ComponentReflection', 'parseAnnotation', '?array'),
                    new MethodReturnType('Nette\Application\UI\ComponentReflection', 'getParameterType', 'array'),
                    new MethodReturnType('Nette\Application\UI\ComponentReflection', 'hasAnnotation', 'bool'),
                    new MethodReturnType('Nette\Application\UI\ComponentReflection', 'getMethods', 'array'),
                    new MethodReturnType(
                        'Nette\Application\UI\Control',
                        'getTemplate',
                        'Nette\Application\UI\ITemplate'
                    ),
                    new MethodReturnType(
                        'Nette\Application\UI\Control',
                        'createTemplate',
                        'Nette\Application\UI\ITemplate'
                    ),
                    new MethodReturnType('Nette\Application\UI\Control', 'templatePrepareFilters', 'void'),
                    new MethodReturnType('Nette\Application\UI\Control', 'flashMessage', 'stdClass'),
                    new MethodReturnType('Nette\Application\UI\Control', 'redrawControl', 'void'),
                    new MethodReturnType('Nette\Application\UI\Control', 'isControlInvalid', 'bool'),
                    new MethodReturnType('Nette\Application\UI\Control', 'getSnippetId', 'string'),
                    new MethodReturnType(
                        'Nette\Application\UI\Form',
                        'getPresenter',
                        '?Nette\Application\UI\Presenter'
                    ),
                    new MethodReturnType('Nette\Application\UI\Form', 'signalReceived', 'void'),
                    new MethodReturnType('Nette\Application\UI\IRenderable', 'redrawControl', 'void'),
                    new MethodReturnType('Nette\Application\UI\IRenderable', 'isControlInvalid', 'bool'),
                    new MethodReturnType('Nette\Application\UI\ITemplate', 'render', 'void'),
                    new MethodReturnType('Nette\Application\UI\ITemplate', 'getFile', '?string'),
                    new MethodReturnType(
                        'Nette\Application\UI\ITemplateFactory',
                        'createTemplate',
                        'Nette\Application\UI\ITemplate'
                    ),
                    new MethodReturnType('Nette\Application\UI\Link', 'getDestination', 'string'),
                    new MethodReturnType('Nette\Application\UI\Link', 'getParameters', 'array'),
                    new MethodReturnType('Nette\Application\UI\Link', '__toString', 'string'),
                    new MethodReturnType('Nette\Application\UI\MethodReflection', 'hasAnnotation', 'bool'),
                    new MethodReturnType('Nette\Application\UI\IStatePersistent', 'loadState', 'void'),
                    new MethodReturnType('Nette\Application\UI\IStatePersistent', 'saveState', 'void'),
                    new MethodReturnType('Nette\Application\UI\ISignalReceiver', 'signalReceived', 'void'),
                    new MethodReturnType(
                        'Nette\Application\Routers\SimpleRouter',
                        'match',
                        '?Nette\Application\Request'
                    ),
                    new MethodReturnType('Nette\Application\Routers\SimpleRouter', 'getDefaults', 'array'),
                    new MethodReturnType('Nette\Application\Routers\SimpleRouter', 'getFlags', 'int'),
                    new MethodReturnType('Nette\Application\LinkGenerator', 'link', 'string'),
                    new MethodReturnType('Nette\Application\MicroPresenter', 'getContext', '?Nette\DI\Container'),
                    new MethodReturnType(
                        'Nette\Application\MicroPresenter',
                        'createTemplate',
                        'Nette\Application\UI\ITemplate'
                    ),
                    new MethodReturnType(
                                                            'Nette\Application\MicroPresenter',
                                                            'redirectUrl',
                                                            'Nette\Application\Responses\RedirectResponse'
                                                        ),
                    new MethodReturnType('Nette\Application\MicroPresenter', 'error', 'void'),
                    new MethodReturnType(
                        'Nette\Application\MicroPresenter',
                        'getRequest',
                        '?Nette\Application\Request'
                    ), ]
            ),
        ]]);
};
