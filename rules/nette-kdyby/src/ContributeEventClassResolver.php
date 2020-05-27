<?php

declare(strict_types=1);

namespace Rector\NetteKdyby;

use PhpParser\Node\Identifier;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\Exception\ShouldNotHappenException;
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

    /**
     * @var ClassNaming
     */
    private $classNaming;

    public function __construct(NodeNameResolver $nodeNameResolver, ClassNaming $classNaming)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->classNaming = $classNaming;
    }

    public function resolveGetterMethodByEventClassAndParam(string $eventClass, Param $param): string
    {
        $getterMethodsWithType = self::GETTER_METHODS_WITH_TYPE_BY_EVENT_CLASS[$eventClass] ?? null;

        $paramType = $param->type;

        // unwrap nullable type
        if ($paramType instanceof NullableType) {
            $paramType = $paramType->type;
        }

        if ($paramType === null || $paramType instanceof Identifier) {
            $variable = $param->var;
            /** @var string $variableName */
            $variableName = $this->nodeNameResolver->getName($variable);

            return 'get' . ucfirst($variableName);
        }

        $type = $this->nodeNameResolver->getName($paramType);
        if ($type === null) {
            throw new ShouldNotHappenException();
        }

        if (isset($getterMethodsWithType[$type])) {
            return $getterMethodsWithType[$type];
        }

        // dummy fallback
        $shortClass = $this->classNaming->getShortName($type);
        return 'get' . $shortClass;
    }
}
