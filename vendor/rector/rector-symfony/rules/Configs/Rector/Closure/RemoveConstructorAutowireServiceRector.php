<?php

declare (strict_types=1);
namespace Rector\Symfony\Configs\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Configs\Rector\Reflection\ConstructorReflectionTypesResolver;
use Rector\Symfony\Enum\SymfonyClass;
use Rector\Symfony\Enum\SymfonyFunctionName;
use Rector\Symfony\NodeAnalyzer\SymfonyPhpClosureDetector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Configs\Rector\Closure\RemoveConstructorAutowireServiceRector\RemoveConstructorAutowireServiceRectorTest
 */
final class RemoveConstructorAutowireServiceRector extends AbstractRector
{
    /**
     * @readonly
     */
    private SymfonyPhpClosureDetector $symfonyPhpClosureDetector;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @readonly
     */
    private ConstructorReflectionTypesResolver $constructorReflectionTypesResolver;
    public function __construct(SymfonyPhpClosureDetector $symfonyPhpClosureDetector, ValueResolver $valueResolver, ConstructorReflectionTypesResolver $constructorReflectionTypesResolver)
    {
        $this->symfonyPhpClosureDetector = $symfonyPhpClosureDetector;
        $this->valueResolver = $valueResolver;
        $this->constructorReflectionTypesResolver = $constructorReflectionTypesResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove service that is passed as arg, but already autowired via constructor', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire();

    $services->set(\App\SomeClass::class)
        ->arg('$someService', ref(\App\SomeService::class));
};

final class SomeClass
{
    public function __construct(private SomeService $someService)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire();

    $services->set(\App\SomeClass::class);
};

final class SomeClass
{
    public function __construct(private SomeService $someService)
    {
    }
}
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
        // services must be autowired, so we can remove explicit service passing
        if (!$this->symfonyPhpClosureDetector->hasDefaultsConfigured($node, 'autowire')) {
            return null;
        }
        $hasChanged = \false;
        $this->traverseNodesWithCallable($node->getStmts(), function (Node $node) use (&$hasChanged): ?Expr {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->isName($node->name, 'arg')) {
                return null;
            }
            $serviceClass = $this->matchSetServicesClass($node);
            if (!is_string($serviceClass)) {
                return null;
            }
            $constructorTypesByParameterName = $this->constructorReflectionTypesResolver->resolve($serviceClass);
            if ($constructorTypesByParameterName === null) {
                return null;
            }
            if ($node->isFirstClassCallable()) {
                return null;
            }
            $argName = $node->getArgs()[0]->value;
            $serviceArgExpr = $node->getArgs()[1]->value;
            if (!$argName instanceof String_) {
                return null;
            }
            $bareParameterName = ltrim($argName->value, '$');
            $knownParameterType = $constructorTypesByParameterName[$bareParameterName] ?? null;
            if (!$knownParameterType instanceof ObjectType) {
                return null;
            }
            if (!$this->isParameterTypeMatchingPassedArgExprClass($serviceArgExpr, $knownParameterType)) {
                return null;
            }
            $hasChanged = \true;
            return $node->var;
        });
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function isSetServices(MethodCall $methodCall): bool
    {
        if (!$this->isName($methodCall->name, 'set')) {
            return \false;
        }
        return $this->isObjectType($methodCall->var, new ObjectType(SymfonyClass::SERVICES_CONFIGURATOR));
    }
    /**
     * $services->set(<this-type>);
     */
    private function matchSetServicesClass(MethodCall $methodCall): ?string
    {
        while ($methodCall instanceof MethodCall) {
            if ($this->isSetServices($methodCall)) {
                break;
            }
            $methodCall = $methodCall->var;
        }
        if (!$methodCall instanceof MethodCall) {
            return null;
        }
        if ($methodCall->isFirstClassCallable()) {
            return null;
        }
        foreach ($methodCall->getArgs() as $arg) {
            if (!$arg->value instanceof ClassConstFetch) {
                continue;
            }
            return $this->valueResolver->getValue($arg->value);
        }
        return null;
    }
    private function isParameterTypeMatchingPassedArgExprClass(Expr $serviceArgExpr, ObjectType $objectType): bool
    {
        if (!$serviceArgExpr instanceof FuncCall) {
            return \false;
        }
        if (!$this->isName($serviceArgExpr->name, SymfonyFunctionName::SERVICE)) {
            return \false;
        }
        if ($serviceArgExpr->isFirstClassCallable()) {
            return \false;
        }
        $dependencyServiceExpr = $serviceArgExpr->getArgs()[0]->value;
        $dependencyService = $this->valueResolver->getValue($dependencyServiceExpr);
        return $dependencyService === $objectType->getClassName();
    }
}
