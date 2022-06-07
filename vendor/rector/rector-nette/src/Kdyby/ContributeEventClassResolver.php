<?php

declare (strict_types=1);
namespace Rector\Nette\Kdyby;

use PhpParser\Node\Identifier;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Naming\Naming\VariableNaming;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class ContributeEventClassResolver
{
    /**
     * @var array<string, array<string, string>>
     */
    private const CONTRIBUTTE_EVENT_GETTER_METHODS_WITH_TYPE = [
        // application
        'RectorPrefix20220607\\Contributte\\Events\\Extra\\Event\\Application\\ShutdownEvent' => ['RectorPrefix20220607\\Nette\\Application\\Application' => 'getApplication', 'Throwable' => 'getThrowable'],
        'RectorPrefix20220607\\Contributte\\Events\\Extra\\Event\\Application\\StartupEvent' => ['RectorPrefix20220607\\Nette\\Application\\Application' => 'getApplication'],
        'RectorPrefix20220607\\Contributte\\Events\\Extra\\Event\\Application\\ErrorEvent' => ['RectorPrefix20220607\\Nette\\Application\\Application' => 'getApplication', 'Throwable' => 'getThrowable'],
        'RectorPrefix20220607\\Contributte\\Events\\Extra\\Event\\Application\\PresenterEvent' => ['RectorPrefix20220607\\Nette\\Application\\Application' => 'getApplication', 'RectorPrefix20220607\\Nette\\Application\\IPresenter' => 'getPresenter'],
        'RectorPrefix20220607\\Contributte\\Events\\Extra\\Event\\Application\\RequestEvent' => ['RectorPrefix20220607\\Nette\\Application\\Application' => 'getApplication', 'RectorPrefix20220607\\Nette\\Application\\Request' => 'getRequest'],
        'RectorPrefix20220607\\Contributte\\Events\\Extra\\Event\\Application\\ResponseEvent' => ['RectorPrefix20220607\\Nette\\Application\\Application' => 'getApplication', 'RectorPrefix20220607\\Nette\\Application\\IResponse' => 'getResponse'],
        // presenter
        'RectorPrefix20220607\\Contributte\\Events\\Extra\\Event\\Application\\PresenterShutdownEvent' => ['RectorPrefix20220607\\Nette\\Application\\IPresenter' => 'getPresenter', 'RectorPrefix20220607\\Nette\\Application\\IResponse' => 'getResponse'],
        'RectorPrefix20220607\\Contributte\\Events\\Extra\\Event\\Application\\PresenterStartupEvent' => ['RectorPrefix20220607\\Nette\\Application\\UI\\Presenter' => 'getPresenter'],
        // nette/security
        'RectorPrefix20220607\\Contributte\\Events\\Extra\\Event\\Security\\LoggedInEvent' => ['RectorPrefix20220607\\Nette\\Security\\User' => 'getUser'],
        'RectorPrefix20220607\\Contributte\\Events\\Extra\\Event\\Security\\LoggedOutEvent' => ['RectorPrefix20220607\\Nette\\Security\\User' => 'getUser'],
        // latte
        'RectorPrefix20220607\\Contributte\\Events\\Extra\\Event\\Latte\\LatteCompileEvent' => ['RectorPrefix20220607\\Latte\\Engine' => 'getEngine'],
        'RectorPrefix20220607\\Contributte\\Events\\Extra\\Event\\Latte\\TemplateCreateEvent' => ['RectorPrefix20220607\\Nette\\Bridges\\ApplicationLatte\\Template' => 'getTemplate'],
    ];
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    public function __construct(NodeNameResolver $nodeNameResolver, StaticTypeMapper $staticTypeMapper, VariableNaming $variableNaming)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->variableNaming = $variableNaming;
    }
    public function resolveGetterMethodByEventClassAndParam(string $eventClass, Param $param) : string
    {
        $getterMethodsWithType = self::CONTRIBUTTE_EVENT_GETTER_METHODS_WITH_TYPE[$eventClass] ?? null;
        $paramType = $param->type;
        // unwrap nullable type
        if ($paramType instanceof NullableType) {
            $paramType = $paramType->type;
        }
        if ($paramType === null || $paramType instanceof Identifier) {
            return $this->resolveParamType($paramType, $param);
        }
        $type = $this->nodeNameResolver->getName($paramType);
        if ($type === null) {
            throw new ShouldNotHappenException();
        }
        // system contribute event
        if (isset($getterMethodsWithType[$type])) {
            return $getterMethodsWithType[$type];
        }
        $staticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($paramType);
        return $this->createGetterFromParamAndStaticType($param, $staticType);
    }
    private function createGetterFromParamAndStaticType(Param $param, Type $type) : string
    {
        $variableName = $this->variableNaming->resolveFromNodeAndType($param, $type);
        if ($variableName === null) {
            throw new ShouldNotHappenException();
        }
        return 'get' . \ucfirst($variableName);
    }
    private function resolveParamType(?Identifier $identifier, Param $param) : string
    {
        $staticType = $identifier === null ? new MixedType() : $this->staticTypeMapper->mapPhpParserNodePHPStanType($identifier);
        return $this->createGetterFromParamAndStaticType($param, $staticType);
    }
}
