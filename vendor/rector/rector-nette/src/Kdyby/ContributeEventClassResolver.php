<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Nette\Kdyby;

use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PhpParser\Node\NullableType;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20220606\Rector\Naming\Naming\VariableNaming;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220606\Rector\StaticTypeMapper\StaticTypeMapper;
final class ContributeEventClassResolver
{
    /**
     * @var array<string, array<string, string>>
     */
    private const CONTRIBUTTE_EVENT_GETTER_METHODS_WITH_TYPE = [
        // application
        'Contributte\\Events\\Extra\\Event\\Application\\ShutdownEvent' => ['Nette\\Application\\Application' => 'getApplication', 'Throwable' => 'getThrowable'],
        'Contributte\\Events\\Extra\\Event\\Application\\StartupEvent' => ['Nette\\Application\\Application' => 'getApplication'],
        'Contributte\\Events\\Extra\\Event\\Application\\ErrorEvent' => ['Nette\\Application\\Application' => 'getApplication', 'Throwable' => 'getThrowable'],
        'Contributte\\Events\\Extra\\Event\\Application\\PresenterEvent' => ['Nette\\Application\\Application' => 'getApplication', 'Nette\\Application\\IPresenter' => 'getPresenter'],
        'Contributte\\Events\\Extra\\Event\\Application\\RequestEvent' => ['Nette\\Application\\Application' => 'getApplication', 'Nette\\Application\\Request' => 'getRequest'],
        'Contributte\\Events\\Extra\\Event\\Application\\ResponseEvent' => ['Nette\\Application\\Application' => 'getApplication', 'Nette\\Application\\IResponse' => 'getResponse'],
        // presenter
        'Contributte\\Events\\Extra\\Event\\Application\\PresenterShutdownEvent' => ['Nette\\Application\\IPresenter' => 'getPresenter', 'Nette\\Application\\IResponse' => 'getResponse'],
        'Contributte\\Events\\Extra\\Event\\Application\\PresenterStartupEvent' => ['Nette\\Application\\UI\\Presenter' => 'getPresenter'],
        // nette/security
        'Contributte\\Events\\Extra\\Event\\Security\\LoggedInEvent' => ['Nette\\Security\\User' => 'getUser'],
        'Contributte\\Events\\Extra\\Event\\Security\\LoggedOutEvent' => ['Nette\\Security\\User' => 'getUser'],
        // latte
        'Contributte\\Events\\Extra\\Event\\Latte\\LatteCompileEvent' => ['Latte\\Engine' => 'getEngine'],
        'Contributte\\Events\\Extra\\Event\\Latte\\TemplateCreateEvent' => ['Nette\\Bridges\\ApplicationLatte\\Template' => 'getTemplate'],
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
