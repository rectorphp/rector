<?php

declare(strict_types=1);

namespace Rector\NetteKdyby;

use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NetteKdyby\Naming\VariableNaming;
use Rector\NetteKdyby\ValueObject\EventAndListenerTree;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;

final class ContributeEventClassResolver
{
    /**
     * @var string[][]
     */
    private const CONTRIBUTTE_EVENT_GETTER_METHODS_WITH_TYPE = [
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
     * @var VariableNaming
     */
    private $variableNaming;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(
        BetterStandardPrinter $betterStandardPrinter,
        NodeNameResolver $nodeNameResolver,
        StaticTypeMapper $staticTypeMapper,
        VariableNaming $variableNaming
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->variableNaming = $variableNaming;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    public function resolveGetterMethodByEventClassAndParam(
        string $eventClass,
        Param $param,
        ?EventAndListenerTree $eventAndListenerTree = null
    ): string {
        $getterMethodsWithType = self::CONTRIBUTTE_EVENT_GETTER_METHODS_WITH_TYPE[$eventClass] ?? null;

        $paramType = $param->type;

        // unwrap nullable type
        if ($paramType instanceof NullableType) {
            $paramType = $paramType->type;
        }

        if ($eventAndListenerTree !== null) {
            $getterMethodBlueprints = $eventAndListenerTree->getGetterMethodBlueprints();

            foreach ($getterMethodBlueprints as $getterMethodBlueprint) {
                if (! $getterMethodBlueprint->getReturnTypeNode() instanceof Name) {
                    continue;
                }

                if ($this->betterStandardPrinter->areNodesEqual(
                    $getterMethodBlueprint->getReturnTypeNode(),
                    $paramType
                )) {
                    return $getterMethodBlueprint->getMethodName();
                }
            }
        }

        if ($paramType === null || $paramType instanceof Identifier) {
            if ($paramType === null) {
                $staticType = new MixedType();
            } else {
                $staticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($paramType);
            }

            return $this->createGetterFromParamAndStaticType($param, $staticType);
        }

        $type = $this->nodeNameResolver->getName($paramType);
        if ($type === null) {
            throw new ShouldNotHappenException();
        }

        // system contribute event
        if (isset($getterMethodsWithType[$type])) {
            return $getterMethodsWithType[$type];
        }

        $paramName = $this->nodeNameResolver->getName($param->var);
        if ($eventAndListenerTree !== null) {
            $getterMethodBlueprints = $eventAndListenerTree->getGetterMethodBlueprints();

            foreach ($getterMethodBlueprints as $getterMethodBlueprint) {
                if ($getterMethodBlueprint->getVariableName() === $paramName) {
                    return $getterMethodBlueprint->getMethodName();
                }
            }
        }

        $staticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($paramType);

        return $this->createGetterFromParamAndStaticType($param, $staticType);
    }

    private function createGetterFromParamAndStaticType(Param $param, Type $type): string
    {
        $variableName = $this->variableNaming->resolveFromNodeAndType($param, $type);
        if ($variableName === null) {
            throw new ShouldNotHappenException();
        }

        return 'get' . ucfirst($variableName);
    }
}
