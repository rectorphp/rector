<?php

declare(strict_types=1);

namespace Rector\NetteKdyby;

use PhpParser\Node\Param;
use Rector\Core\Exception\NotImplementedException;
use Rector\NodeNameResolver\NodeNameResolver;

final class ContributeEventClassResolver
{
    /**
     * @var string[][]
     */
    private const GETTER_METHODS_WITH_TYPE_BY_EVENT_CLASS = [
        // application
        'Contributte\Events\Extra\Event\Application\ShutdownEvent' => [
            'Nette\Application\Application' => 'getApplication',
            'Throwable' => 'getThrowable',
        ],
        'Contributte\Events\Extra\Event\Application\StartupEvent' => [
            'Nette\Application\Application' => 'getApplication',
        ],
        'Contributte\Events\Extra\Event\Application\ErrorEvent' => [
            'Nette\Application\Application' => 'getApplication',
            'Throwable' => 'getThrowable',
        ],
        'Contributte\Events\Extra\Event\Application\PresenterEvent' => [
            'Nette\Application\Application' => 'getApplication',
            'Nette\Application\IPresenter' => 'getPresenter',
        ],
        'Contributte\Events\Extra\Event\Application\RequestEvent' => [
            'Nette\Application\Application' => 'getApplication',
            'Nette\Application\Request' => 'getRequest',
        ],
        'Contributte\Events\Extra\Event\Application\ResponseEvent' => [
            'Nette\Application\Application' => 'getApplication',
            'Nette\Application\IResponse' => 'getResponse',
        ],
        // presenter
        'Contributte\Events\Extra\Event\Application\PresenterShutdownEvent' => [
            'Nette\Application\IPresenter' => 'getPresenter',
            'Nette\Application\IResponse' => 'getResponse',
        ],
        'Contributte\Events\Extra\Event\Application\PresenterStartupEvent' => [
            'Nette\Application\UI\Presenter' => 'getPresenter',
        ],
        // nette/security
        'Contributte\Events\Extra\Event\Security\LoggedInEvent' => [
            'Nette\Security\User' => 'getUser',
        ],
        'Contributte\Events\Extra\Event\Security\LoggedOutEvent' => [
            'Nette\Security\User' => 'getUser',
        ],
        // latte
        'Contributte\Events\Extra\Event\Latte\LatteCompileEvent' => [
            'Latte\Engine' => 'getEngine',
        ],
        'Contributte\Events\Extra\Event\Latte\TemplateCreateEvent' => [
            'Nette\Bridges\ApplicationLatte\Template' => 'getTemplate',
        ],
    ];

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function resolveGetterMethodByEventClassAndParam(string $eventClass, Param $param): string
    {
        $getterMethodsWithType = self::GETTER_METHODS_WITH_TYPE_BY_EVENT_CLASS[$eventClass] ?? null;

        if ($param->type === null) {
            throw new NotImplementedException();
        }

        $type = $this->nodeNameResolver->getName($param->type);
        if (isset($getterMethodsWithType[$type])) {
            return $getterMethodsWithType[$type];
        }

        throw new NotImplementedException();
    }
}
