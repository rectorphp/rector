<?php

declare (strict_types=1);
namespace Rector\Defluent\Skipper;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Defluent\Contract\ValueObject\FirstCallFactoryAwareInterface;
use Rector\Defluent\NodeAnalyzer\FluentCallStaticTypeResolver;
use Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer;
use Rector\Defluent\NodeAnalyzer\GetterMethodCallAnalyzer;
use Rector\Defluent\NodeAnalyzer\SameClassMethodCallAnalyzer;
use Rector\Defluent\ValueObject\AssignAndRootExpr;
use Rector\Defluent\ValueObject\FirstAssignFluentCall;
final class FluentMethodCallSkipper
{
    /**
     * Skip query and builder
     * @see https://ocramius.github.io/blog/fluent-interfaces-are-evil/ "When does a fluent interface make sense?
     *
     * @var class-string[]
     */
    private const ALLOWED_FLUENT_TYPES = [
        // symfony
        'Symfony\\Component\\DependencyInjection\\Loader\\Configurator\\AbstractConfigurator',
        'Symfony\\Component\\Finder\\Finder',
        // doctrine
        'Doctrine\\ORM\\QueryBuilder',
        // nette
        'Nette\\Utils\\Finder',
        'Nette\\Forms\\Controls\\BaseControl',
        'Nette\\DI\\ContainerBuilder',
        'Nette\\DI\\Definitions\\Definition',
        'Nette\\DI\\Definitions\\ServiceDefinition',
        'PHPStan\\Analyser\\Scope',
        'DateTimeInterface',
    ];
    /**
     * @var \Rector\Defluent\NodeAnalyzer\FluentCallStaticTypeResolver
     */
    private $fluentCallStaticTypeResolver;
    /**
     * @var \Rector\Defluent\NodeAnalyzer\SameClassMethodCallAnalyzer
     */
    private $sameClassMethodCallAnalyzer;
    /**
     * @var \Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer
     */
    private $fluentChainMethodCallNodeAnalyzer;
    /**
     * @var \Rector\Defluent\NodeAnalyzer\GetterMethodCallAnalyzer
     */
    private $getterMethodCallAnalyzer;
    public function __construct(\Rector\Defluent\NodeAnalyzer\FluentCallStaticTypeResolver $fluentCallStaticTypeResolver, \Rector\Defluent\NodeAnalyzer\SameClassMethodCallAnalyzer $sameClassMethodCallAnalyzer, \Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer $fluentChainMethodCallNodeAnalyzer, \Rector\Defluent\NodeAnalyzer\GetterMethodCallAnalyzer $getterMethodCallAnalyzer)
    {
        $this->fluentCallStaticTypeResolver = $fluentCallStaticTypeResolver;
        $this->sameClassMethodCallAnalyzer = $sameClassMethodCallAnalyzer;
        $this->fluentChainMethodCallNodeAnalyzer = $fluentChainMethodCallNodeAnalyzer;
        $this->getterMethodCallAnalyzer = $getterMethodCallAnalyzer;
    }
    public function shouldSkipRootMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        if (!$this->fluentChainMethodCallNodeAnalyzer->isLastChainMethodCall($methodCall)) {
            return \true;
        }
        return $this->getterMethodCallAnalyzer->isGetterMethodCall($methodCall);
    }
    public function shouldSkipFirstAssignFluentCall(\Rector\Defluent\ValueObject\FirstAssignFluentCall $firstAssignFluentCall) : bool
    {
        $calleeUniqueTypes = $this->fluentCallStaticTypeResolver->resolveCalleeUniqueTypes($firstAssignFluentCall->getFluentMethodCalls());
        if (!$this->sameClassMethodCallAnalyzer->isCorrectTypeCount($calleeUniqueTypes, $firstAssignFluentCall)) {
            return \true;
        }
        $calleeUniqueType = $this->resolveCalleeUniqueType($firstAssignFluentCall, $calleeUniqueTypes);
        return $this->isAllowedType($calleeUniqueType);
    }
    /**
     * @param MethodCall[] $fluentMethodCalls
     */
    public function shouldSkipMethodCalls(\Rector\Defluent\ValueObject\AssignAndRootExpr $assignAndRootExpr, array $fluentMethodCalls) : bool
    {
        $calleeUniqueTypes = $this->fluentCallStaticTypeResolver->resolveCalleeUniqueTypes($fluentMethodCalls);
        if (!$this->sameClassMethodCallAnalyzer->isCorrectTypeCount($calleeUniqueTypes, $assignAndRootExpr)) {
            return \true;
        }
        $calleeUniqueType = $this->resolveCalleeUniqueType($assignAndRootExpr, $calleeUniqueTypes);
        return $this->isAllowedType($calleeUniqueType);
    }
    /**
     * @param string[] $calleeUniqueTypes
     */
    private function resolveCalleeUniqueType(\Rector\Defluent\Contract\ValueObject\FirstCallFactoryAwareInterface $firstCallFactoryAware, array $calleeUniqueTypes) : string
    {
        if (!$firstCallFactoryAware->isFirstCallFactory()) {
            return $calleeUniqueTypes[0];
        }
        return $calleeUniqueTypes[1] ?? $calleeUniqueTypes[0];
    }
    private function isAllowedType(string $class) : bool
    {
        $objectType = new \PHPStan\Type\ObjectType($class);
        foreach (self::ALLOWED_FLUENT_TYPES as $allowedFluentType) {
            $allowedObjectType = new \PHPStan\Type\ObjectType($allowedFluentType);
            if ($allowedObjectType->isSuperTypeOf($objectType)->yes()) {
                return \true;
            }
        }
        return \false;
    }
}
