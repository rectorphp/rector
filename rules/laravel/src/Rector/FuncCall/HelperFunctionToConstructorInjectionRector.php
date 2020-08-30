<?php

declare(strict_types=1);

namespace Rector\Laravel\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\Transform\ValueObject\ArrayFunctionToMethodCall;
use Rector\Transform\ValueObject\FunctionToMethodCall;
use Webmozart\Assert\Assert;

/**
 * @see https://github.com/laravel/framework/blob/78828bc779e410e03cc6465f002b834eadf160d2/src/Illuminate/Foundation/helpers.php#L959
 * @see https://gist.github.com/barryvdh/bb6ffc5d11e0a75dba67
 *
 * @see \Rector\Laravel\Tests\Rector\FuncCall\HelperFunctionToConstructorInjectionRector\HelperFunctionToConstructorInjectionRectorTest
 */
final class HelperFunctionToConstructorInjectionRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const FUNCTIONS_TO_METHOD_CALLS = 'functions_to_method_calls';

    /**
     * @var string
     */
    public const ARRAY_FUNCTIONS_TO_METHOD_CALLS = 'array_functions_to_method_calls';

    /**
     * @var FunctionToMethodCall[]
     */
    private $functionToMethodCalls = [];

    /**
     * @var ArrayFunctionToMethodCall[]
     */
    private $arrayFunctionsToMethodCalls = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Move help facade-like function calls to constructor injection', [
            new ConfiguredCodeSample(
                <<<'PHP'
class SomeController
{
    public function action()
    {
        $template = view('template.blade');
        $viewFactory = view();
    }
}
PHP
                ,
                <<<'PHP'
class SomeController
{
    /**
     * @var \Illuminate\Contracts\View\Factory
     */
    private $viewFactory;

    public function __construct(\Illuminate\Contracts\View\Factory $viewFactory)
    {
        $this->viewFactory = $viewFactory;
    }

    public function action()
    {
        $template = $this->viewFactory->make('template.blade');
        $viewFactory = $this->viewFactory;
    }
}
PHP
                ,
                [
                    self::FUNCTIONS_TO_METHOD_CALLS => [
                        new FunctionToMethodCall('view', 'Illuminate\Contracts\View\Factory', 'viewFactory', 'make'),
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkipFuncCall($node)) {
            return null;
        }

        /** @var Class_ $classLike */
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);

        foreach ($this->functionToMethodCalls as $functionToMethodCall) {
            if (! $this->isName($node, $functionToMethodCall->getFunction())) {
                continue;
            }

            return $this->refactorFuncCallToMethodCall($functionToMethodCall, $classLike, $node);
        }

        foreach ($this->arrayFunctionsToMethodCalls as $arrayFunctionsToMethodCall) {
            if (! $this->isName($node, $arrayFunctionsToMethodCall->getFunction())) {
                continue;
            }

            return $this->refactorArrayFunctionToMethodCall($arrayFunctionsToMethodCall, $node, $classLike);
        }

        return null;
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $functionToMethodCalls = $configuration[self::FUNCTIONS_TO_METHOD_CALLS] ?? [];
        Assert::allIsInstanceOf($functionToMethodCalls, FunctionToMethodCall::class);
        $this->functionToMethodCalls = $functionToMethodCalls;

        $arrayFunctionsToMethodCalls = $configuration[self::ARRAY_FUNCTIONS_TO_METHOD_CALLS] ?? [];
        Assert::allIsInstanceOf($arrayFunctionsToMethodCalls, ArrayFunctionToMethodCall::class);
        $this->arrayFunctionsToMethodCalls = $arrayFunctionsToMethodCalls;
    }

    private function shouldSkipFuncCall(FuncCall $funcCall): bool
    {
        // we can inject only in class context
        $classLike = $funcCall->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return true;
        }

        /** @var ClassMethod|null $classMethod */
        $classMethod = $funcCall->getAttribute(AttributeKey::METHOD_NODE);
        if ($classMethod === null) {
            return true;
        }

        return $classMethod->isStatic();
    }

    private function isFunctionToMethodCallWithArgs(FuncCall $funcCall, FunctionToMethodCall $functionChange): bool
    {
        if ($functionChange->getMethodIfArgs() === null) {
            return false;
        }

        return count($funcCall->args) >= 1;
    }

    /**
     * @return PropertyFetch|MethodCall
     */
    private function refactorFuncCallToMethodCall(
        FunctionToMethodCall $functionToMethodCall,
        Class_ $classLike,
        FuncCall $funcCall
    ): ?Node {
        $fullyQualifiedObjectType = new FullyQualifiedObjectType($functionToMethodCall->getClass());
        $this->addConstructorDependencyToClass(
            $classLike,
            $fullyQualifiedObjectType,
            $functionToMethodCall->getProperty()
        );

        $propertyFetchNode = $this->createPropertyFetch('this', $functionToMethodCall->getProperty());

        if (count($funcCall->args) === 0) {
            if ($functionToMethodCall->getMethodIfNoArgs()) {
                return new MethodCall($propertyFetchNode, $functionToMethodCall->getMethodIfNoArgs());
            }

            return $propertyFetchNode;
        }

        if ($this->isFunctionToMethodCallWithArgs($funcCall, $functionToMethodCall)) {
            return new MethodCall($propertyFetchNode, $functionToMethodCall->getMethodIfArgs(), $funcCall->args);
        }

        return null;
    }

    /**
     * @return PropertyFetch|MethodCall|null
     */
    private function refactorArrayFunctionToMethodCall(
        ArrayFunctionToMethodCall $arrayFunctionsToMethodCall,
        FuncCall $funcCall,
        Class_ $class
    ): ?Node {
        $propertyFetch = $this->createPropertyFetch('this', $arrayFunctionsToMethodCall->getProperty());

        $fullyQualifiedObjectType = new FullyQualifiedObjectType($arrayFunctionsToMethodCall->getClass());

        $this->addConstructorDependencyToClass(
            $class,
            $fullyQualifiedObjectType,
            $arrayFunctionsToMethodCall->getProperty()
        );

        return $this->createMethodCallArrayFunctionToMethodCall(
            $funcCall,
            $arrayFunctionsToMethodCall,
            $propertyFetch
        );
    }

    /**
     * @return PropertyFetch|MethodCall|null
     */
    private function createMethodCallArrayFunctionToMethodCall(
        FuncCall $funcCall,
        ArrayFunctionToMethodCall $arrayFunctionToMethodCall,
        PropertyFetch $propertyFetch
    ): ?Node {
        if (count($funcCall->args) === 0) {
            return $propertyFetch;
        }

        if ($arrayFunctionToMethodCall->getArrayMethod() && $this->isArrayType($funcCall->args[0]->value)) {
            return new MethodCall($propertyFetch, $arrayFunctionToMethodCall->getArrayMethod(), $funcCall->args);
        }

        if ($arrayFunctionToMethodCall->getNonArrayMethod() && ! $this->isArrayType($funcCall->args[0]->value)) {
            return new MethodCall($propertyFetch, $arrayFunctionToMethodCall->getNonArrayMethod(), $funcCall->args);
        }

        return null;
    }
}
