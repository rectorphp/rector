<?php

declare (strict_types=1);
namespace Rector\Symfony\Configs\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Enum\SymfonyClass;
use Rector\Symfony\Enum\SymfonyFunctionName;
use Rector\Symfony\NodeAnalyzer\SymfonyPhpClosureDetector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Configs\Rector\Closure\ServiceArgsToServiceNamedArgRector\ServiceArgsToServiceNamedArgRectorTest
 */
final class ServiceArgsToServiceNamedArgRector extends AbstractRector
{
    /**
     * @readonly
     */
    private SymfonyPhpClosureDetector $symfonyPhpClosureDetector;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(SymfonyPhpClosureDetector $symfonyPhpClosureDetector, ReflectionProvider $reflectionProvider, ValueResolver $valueResolver)
    {
        $this->symfonyPhpClosureDetector = $symfonyPhpClosureDetector;
        $this->reflectionProvider = $reflectionProvider;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Converts order-dependent arguments args() to named arg() call', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SomeClass::class)
        ->args(['some_value']);
};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SomeClass::class)
        ->arg('$someCtorParameter', 'some_value');
};
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Closure::class];
    }
    /**
     * @param Closure $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->symfonyPhpClosureDetector->detect($node)) {
            return null;
        }
        $hasChanged = \false;
        $this->traverseNodesWithCallable($node->stmts, function (Node $node) use (&$hasChanged): ?MethodCall {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->isServiceArgsMethodCall($node)) {
                return null;
            }
            $serviceClass = $this->resolveServiceClass($node);
            // impossible to resolve
            if ($serviceClass === null) {
                return null;
            }
            if (!$this->reflectionProvider->hasClass($serviceClass)) {
                return null;
            }
            $constructorParameterNames = $this->resolveConstructorParameterNames($serviceClass);
            $mainArgMethodCall = $this->createMainArgMethodCall($node, $constructorParameterNames);
            if (!$mainArgMethodCall instanceof MethodCall) {
                return null;
            }
            $hasChanged = \true;
            return $mainArgMethodCall;
        });
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function resolveServiceClass(MethodCall $methodCall): ?string
    {
        while ($methodCall->var instanceof MethodCall) {
            $methodCall = $methodCall->var;
        }
        if (!$this->isName($methodCall->name, 'set')) {
            return null;
        }
        $servicesSetArgs = $methodCall->getArgs();
        foreach ($servicesSetArgs as $serviceSetArg) {
            if (!$serviceSetArg->value instanceof ClassConstFetch) {
                continue;
            }
            return $this->valueResolver->getValue($serviceSetArg->value);
        }
        return null;
    }
    /**
     * @return string[]
     */
    private function resolveConstructorParameterNames(string $serviceClass): array
    {
        $serviceClassReflection = $this->reflectionProvider->getClass($serviceClass);
        if (!$serviceClassReflection->hasConstructor()) {
            return [];
        }
        $constructorParameterNames = [];
        $extendedMethodReflection = $serviceClassReflection->getConstructor();
        $extendedParametersAcceptor = ParametersAcceptorSelector::combineAcceptors($extendedMethodReflection->getVariants());
        foreach ($extendedParametersAcceptor->getParameters() as $extendedParameterReflection) {
            $constructorParameterNames[] = '$' . $extendedParameterReflection->getName();
        }
        return $constructorParameterNames;
    }
    private function isServiceArgsMethodCall(MethodCall $methodCall): bool
    {
        if (!$this->isName($methodCall->name, 'args')) {
            return \false;
        }
        return $this->isObjectType($methodCall->var, new ObjectType(SymfonyClass::SERVICE_CONFIGURATOR));
    }
    private function createArgMethodCall(string $parameterName, Expr $expr, ?MethodCall $argMethodCall, MethodCall $methodCall): MethodCall
    {
        $argArgs = [new Arg(new String_($parameterName)), new Arg($expr)];
        $callerExpr = $argMethodCall instanceof MethodCall ? $argMethodCall : $methodCall->var;
        return new MethodCall($callerExpr, 'arg', $argArgs);
    }
    /**
     * @param string[] $constructorParameterNames
     */
    private function createMainArgMethodCall(MethodCall $methodCall, array $constructorParameterNames): ?Expr
    {
        if ($constructorParameterNames === []) {
            return null;
        }
        $argMethodCall = null;
        foreach ($methodCall->getArgs() as $arg) {
            if (!$arg->value instanceof Array_) {
                return null;
            }
            $array = $arg->value;
            foreach ($array->items as $key => $arrayItem) {
                if (!$arrayItem instanceof ArrayItem) {
                    continue;
                }
                $arrayItemValue = $arrayItem->value;
                $parameterPosition = $this->resolveParameterPosition($arrayItem, $key);
                if ($this->isExprAutowired($arrayItemValue)) {
                    if (!$argMethodCall instanceof Expr) {
                        $argMethodCall = $methodCall->var;
                    }
                    continue;
                }
                $argMethodCall = $this->createArgMethodCall($constructorParameterNames[$parameterPosition], $arrayItemValue, $argMethodCall, $methodCall);
            }
        }
        return $argMethodCall;
    }
    private function resolveParameterPosition(ArrayItem $arrayItem, int $key): int
    {
        if ($arrayItem->key instanceof Expr) {
            return $this->valueResolver->getValue($arrayItem->key);
        }
        // fallback in case of empty array item
        return $key;
    }
    private function isExprAutowired(Expr $expr): bool
    {
        if (!$expr instanceof FuncCall) {
            return \false;
        }
        if (!$this->isNames($expr->name, [SymfonyFunctionName::SERVICE, SymfonyFunctionName::REF])) {
            return \false;
        }
        $firstArg = $expr->getArgs()[0];
        if (!$firstArg->value instanceof String_) {
            return \false;
        }
        return $this->valueResolver->isValue($firstArg->value, 'request_stack');
    }
}
