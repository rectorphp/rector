<?php

declare(strict_types=1);

use Rector\Generic\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    # scalar type hints, see https://github.com/nette/security/commit/84024f612fb3f55f5d6e3e3e28eef1ad0388fa56
    $services->set(AddReturnTypeDeclarationRector::class)
        ->call('configure', [[
            AddReturnTypeDeclarationRector::TYPEHINT_FOR_METHOD_BY_CLASS => [
                'Nette\Mail\Mailer' => [
                    'send' => 'void',
                ],
                'Nette\Forms\Rendering\DefaultFormRenderer' => [
                    'renderControl' => 'Nette\Utils\Html',
                ],
                'Nette\Caching\Cache' => [
                    'generateKey' => 'string',
                ],
                'Nette\Security\IResource' => [
                    'getResourceId' => 'string',
                ],
                'Nette\Security\IAuthenticator' => [
                    'authenticate' => 'Nette\Security\IIdentity',
                ],
                'Nette\Security\IAuthorizator' => [
                    'isAllowed' => 'bool',
                ],
                'Nette\Security\Identity' => [
                    'getData' => 'array',
                ],
                'Nette\Security\IIdentity' => [
                    'getRoles' => 'array',
                ],
                'Nette\Security\User' => [
                    'getStorage' => 'Nette\Security\IUserStorage',
                    'login' => 'void',
                    'logout' => 'void',
                    'isLoggedIn' => 'bool',
                    'getIdentity' => '?Nette\Security\IIdentity',
                    'getAuthenticator' => '?Nette\Security\IAuthenticator',
                    'getAuthorizator' => '?Nette\Security\IAuthorizator',
                    'getLogoutReason' => '?int',
                    'getRoles' => 'array',
                    'isInRole' => 'bool',
                    'isAllowed' => 'bool',
                ],
                'Nette\Security\IUserStorage' => [
                    'isAuthenticated' => 'bool',
                    'getIdentity' => '?Nette\Security\IIdentity',
                    'getLogoutReason' => '?int',
                ],
                # scalar type hints, see https://github.com/nette/component-model/commit/f69df2ca224cad7b07f1c8835679393263ea6771
                'Nette\ComponentModel\Component' => [
                    'lookup' => 'Nette\ComponentModel\IComponent',
                    'lookupPath' => '?string',
                    'monitor' => 'void',
                    'unmonitor' => 'void',
                    'attached' => 'void',
                    'detached' => 'void',
                    'getName' => '?string',
                ],
                'Nette\ComponentModel\IComponent' => [
                    'getName' => '?string',
                    'getParent' => '?Nette\ComponentModel\IContainer',
                ],
                'Nette\ComponentModel\Container' => [
                    'removeComponent' => 'void',
                    'getComponent' => '?Nette\ComponentModel\IComponent',
                    'createComponent' => '?Nette\ComponentModel\IComponent',
                    'getComponents' => 'Iterator',
                    'validateChildComponent' => 'void',
                    '_isCloning' => '?Nette\ComponentModel\IComponent',
                ],
                'Nette\ComponentModel\IContainer' => [
                    'removeComponent' => 'void',
                    'getComponent' => '?Nette\ComponentModel\IContainer',
                    'getComponents' => 'Iterator',
                ],
                # scalar type hints, see https://github.com/nette/application/commit/b71d69c507f90b48fbc1e40447d451b4b5c6f063
                'Nette\Application\Application' => [
                    'run' => 'void',
                    'createInitialRequest' => 'Nette\Application\Request',
                    'processRequest' => 'void',
                    'processException' => 'void',
                    'getRequests' => 'array',
                    'getPresenter' => '?Nette\Application\IPresenter',
                    'getRouter' => '?Nette\Application\IRouter',
                    'getPresenterFactory' => '?Nette\Application\IPresenterFactory',
                ],
                'Nette\Application\Helpers' => [
                    'splitName' => 'array',
                ],
                'Nette\Application\IPresenter' => [
                    'run' => 'Nette\Application\IResponse',
                ],
                'Nette\Application\IPresenterFactory' => [
                    'getPresenterClass' => 'string',
                    'createPresenter' => 'Nette\Application\IPresenter',
                ],
                'Nette\Application\PresenterFactory' => [
                    'formatPresenterClass' => 'string',
                    'unformatPresenterClass' => '?string',
                ],
                'Nette\Application\IResponse' => [
                    'send' => 'void',
                ],
                'Nette\Application\Responses\FileResponse' => [
                    'getFile' => 'string',
                    'getName' => 'string',
                    'getContentType' => 'string',
                ],
                'Nette\Application\Responses\ForwardResponse' => [
                    'getRequest' => 'Nette\Application\Request',
                ],
                'Nette\Application\Request' => [
                    'getPresenterName' => 'string',
                    'getParameters' => 'array',
                    'getFiles' => 'array',
                    'getMethod' => '?string',
                    'isMethod' => 'bool',
                    'hasFlag' => 'bool',
                ],
                'Nette\Application\RedirectResponse' => [
                    'getUrl' => 'string',
                    'getCode' => 'int',
                ],
                'Nette\Application\JsonResponse' => [
                    'getContentType' => 'string',
                ],
                'Nette\Application\IRouter' => [
                    'match' => '?Nette\Application\Request',
                    'constructUrl' => '?string',
                ],
                'Nette\Application\Routers\Route' => [
                    'getMask' => 'string',
                    'getDefaults' => 'array',
                    'getFlags' => 'int',
                    'getTargetPresenters' => '?array',
                ],
                'Nette\Application\Routers\RouteList' => [
                    'warmupCache' => 'void',
                    'offsetSet' => 'void',
                    'getModule' => '?string',
                ],
                'Nette\Application\Routers\CliRouter' => [
                    'getDefaults' => 'array',
                ],
                'Nette\Application\UI\Component' => [
                    'getPresenter' => '?Nette\Application\UI\Presenter',
                    'getUniqueId' => 'string',
                    'tryCall' => 'bool',
                    'checkRequirements' => 'void',
                    'getReflection' => 'Nette\Application\UI\ComponentReflection',
                    'loadState' => 'void',
                    'saveState' => 'void',
                    'getParameters' => 'array',
                    'getParameterId' => 'string',
                    'getPersistentParams' => 'array',
                    'signalReceived' => 'void',
                    'formatSignalMethod' => 'void',
                    'link' => 'string',
                    'lazyLink' => 'Nette\Application\UI\Link',
                    'isLinkCurrent' => 'bool',
                    'redirect' => 'void',
                    'redirectPermanent' => 'void',
                    'offsetSet' => 'void',
                    'offsetGet' => 'Nette\ComponentModel\IComponent',
                    'offsetExists' => 'void',
                    'offsetUnset' => 'void',
                ],
                'Nette\Application\UI\Presenter' => [
                    'getRequest' => 'Nette\Application\Request',
                    'getPresenter' => 'Nette\Application\UI\Presenter',
                    'getUniqueId' => 'string',
                    'checkRequirements' => 'void',
                    'processSignal' => 'void',
                    'getSignal' => '?array',
                    'isSignalReceiver' => 'bool',
                    'getAction' => 'string',
                    'changeAction' => 'void',
                    'getView' => 'string',
                    'sendTemplate' => 'void',
                    'findLayoutTemplateFile' => '?string',
                    'formatLayoutTemplateFiles' => 'array',
                    'formatTemplateFiles' => 'array',
                    'formatActionMethod' => 'string',
                    'formatRenderMethod' => 'string',
                    'createTemplate' => 'Nette\Application\UI\ITemplate',
                    'getPayload' => 'stdClass',
                    'isAjax' => 'bool',
                    'sendPayload' => 'void',
                    'sendJson' => 'void',
                    'sendResponse' => 'void',
                    'terminate' => 'void',
                    'forward' => 'void',
                    'redirectUrl' => 'void',
                    'error' => 'void',
                    'getLastCreatedRequest' => '?Nette\Application\Request',
                    'getLastCreatedRequestFlag' => 'bool',
                    'canonicalize' => 'void',
                    'lastModified' => 'void',
                    'createRequest' => '?string',
                    'argsToParams' => 'void',
                    'handleInvalidLink' => 'string',
                    'storeRequest' => 'string',
                    'restoreRequest' => 'void',
                    'getPersistentComponents' => 'array',
                    'getGlobalState' => 'array',
                    'saveGlobalState' => 'void',
                    'initGlobalParameters' => 'void',
                    'popGlobalParameters' => 'array',
                    'getFlashKey' => '?string',
                    'hasFlashSession' => 'bool',
                    'getFlashSession' => 'Nette\Http\SessionSection',
                    'getContext' => 'Nette\DI\Container',
                    'getHttpRequest' => 'Nette\Http\IRequest',
                    'getHttpResponse' => 'Nette\Http\IResponse',
                    'getUser' => 'Nette\Security\User',
                    'getTemplateFactory' => 'Nette\Application\UI\ITemplateFactory',
                ],
                'Nette\Application\Exception\BadRequestException' => [
                    'getHttpCode' => 'int',
                ],
                'Nette\Bridges\ApplicationDI\LatteExtension' => [
                    'addMacro' => 'void',
                ],
                'Nette\Bridges\ApplicationDI\PresenterFactoryCallback' => [
                    '__invoke' => 'Nette\Application\IPresenter',
                ],
                'Nette\Bridges\ApplicationLatte\ILatteFactory' => [
                    'create' => 'Latte\Engine',
                ],
                'Nette\Bridges\ApplicationLatte\Template' => [
                    'getLatte' => 'Latte\Engine',
                    'render' => 'void',
                    '__toString' => 'string',
                    'getFile' => '?string',
                    'getParameters' => 'array',
                    '__set' => 'void',
                    '__unset' => 'void',
                ],
                'Nette\Bridges\ApplicationLatte\TemplateFactory' => [
                    'createTemplate' => 'Nette\Application\UI\ITemplate',
                ],
                'Nette\Bridges\ApplicationLatte\UIMacros' => [
                    'initialize' => 'void',
                ],
                'Nette\Bridges\ApplicationTracy\RoutingPanel' => [
                    'initializePanel' => 'void',
                    'getTab' => 'string',
                    'getPanel' => 'string',
                ],
                'Nette\Bridges\ApplicationLatte\UIRuntime' => [
                    'initialize' => 'void',
                ],
                'Nette\Application\UI\ComponentReflection' => [
                    'getPersistentParams' => 'array',
                    'getPersistentComponents' => 'array',
                    'hasCallableMethod' => 'bool',
                    'combineArgs' => 'array',
                    'convertType' => 'bool',
                    'parseAnnotation' => '?array',
                    'getParameterType' => 'array',
                    'hasAnnotation' => 'bool',
                    'getMethods' => 'array',
                ],
                'Nette\Application\UI\Control' => [
                    'getTemplate' => 'Nette\Application\UI\ITemplate',
                    'createTemplate' => 'Nette\Application\UI\ITemplate',
                    'templatePrepareFilters' => 'void',
                    'flashMessage' => 'stdClass',
                    'redrawControl' => 'void',
                    'isControlInvalid' => 'bool',
                    'getSnippetId' => 'string',
                ],
                'Nette\Application\UI\Form' => [
                    'getPresenter' => '?Nette\Application\UI\Presenter',
                    'signalReceived' => 'void',
                ],
                'Nette\Application\UI\IRenderable' => [
                    'redrawControl' => 'void',
                    'isControlInvalid' => 'bool',
                ],
                'Nette\Application\UI\ITemplate' => [
                    'render' => 'void',
                    'getFile' => '?string',
                ],
                'Nette\Application\UI\ITemplateFactory' => [
                    'createTemplate' => 'Nette\Application\UI\ITemplate',
                ],
                'Nette\Application\UI\Link' => [
                    'getDestination' => 'string',
                    'getParameters' => 'array',
                    '__toString' => 'string',
                ],
                'Nette\Application\UI\MethodReflection' => [
                    'hasAnnotation' => 'bool',
                ],
                'Nette\Application\UI\IStatePersistent' => [
                    'loadState' => 'void',
                    'saveState' => 'void',
                ],
                'Nette\Application\UI\ISignalReceiver' => [
                    'signalReceived' => 'void',
                ],
                'Nette\Application\Routers\SimpleRouter' => [
                    'match' => '?Nette\Application\Request',
                    'getDefaults' => 'array',
                    'getFlags' => 'int',
                ],
                'Nette\Application\LinkGenerator' => [
                    'link' => 'string',
                ],
                'Nette\Application\MicroPresenter' => [
                    'getContext' => '?Nette\DI\Container',
                    'createTemplate' => 'Nette\Application\UI\ITemplate',
                    'redirectUrl' => 'Nette\Application\Responses\RedirectResponse',
                    'error' => 'void',
                    'getRequest' => '?Nette\Application\Request',
                ],
            ],
        ]]);
};
