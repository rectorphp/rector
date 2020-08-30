<?php

declare(strict_types=1);

namespace Rector\Transform\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\Transform\ValueObject\ArgumentFuncCallToMethodCall;
use Rector\Transform\ValueObject\ArrayFuncCallToMethodCall;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Transform\Tests\Rector\FuncCall\ArgumentFuncCallToMethodCallRector\ArgumentFuncCallToMethodCallRectorTest
 */
final class ArgumentFuncCallToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
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
     * @var ArgumentFuncCallToMethodCall[]
     */
    private $argumentFuncCallToMethodCalls = [];

    /**
     * @var ArrayFuncCallToMethodCall[]
     */
    private $arrayFunctionsToMethodCalls = [];

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    public function __construct(PropertyNaming $propertyNaming)
    {
        $this->propertyNaming = $propertyNaming;
    }

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
                        new ArgumentFuncCallToMethodCall('view', 'Illuminate\Contracts\View\Factory', 'make'),
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

        foreach ($this->argumentFuncCallToMethodCalls as $functionToMethodCall) {
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
        Assert::allIsInstanceOf($functionToMethodCalls, ArgumentFuncCallToMethodCall::class);
        $this->argumentFuncCallToMethodCalls = $functionToMethodCalls;

        $arrayFunctionsToMethodCalls = $configuration[self::ARRAY_FUNCTIONS_TO_METHOD_CALLS] ?? [];
        Assert::allIsInstanceOf($arrayFunctionsToMethodCalls, ArrayFuncCallToMethodCall::class);
        $this->arrayFunctionsToMethodCalls = $arrayFunctionsToMethodCalls;
    }

    private function shouldSkipFuncCall(FuncCall $funcCall): bool
    {
        // we can inject only in injectable class method  context
        /** @var ClassMethod|null $classMethod */
        $classMethod = $funcCall->getAttribute(AttributeKey::METHOD_NODE);
        if ($classMethod === null) {
            return true;
        }

        return $classMethod->isStatic();
    }

    /**
     * @return PropertyFetch|MethodCall
     */
    private function refactorFuncCallToMethodCall(
        ArgumentFuncCallToMethodCall $argumentFuncCallToMethodCall,
        Class_ $class,
        FuncCall $funcCall
    ): ?Node {
        $fullyQualifiedObjectType = new FullyQualifiedObjectType($argumentFuncCallToMethodCall->getClass());
        $propertyName = $this->propertyNaming->getExpectedNameFromType($fullyQualifiedObjectType);

        if ($propertyName === null) {
            throw new ShouldNotHappenException();
        }

        $this->addConstructorDependencyToClass($class, $fullyQualifiedObjectType, $propertyName);

        $propertyFetchNode = $this->createPropertyFetch('this', $propertyName);

        if (count($funcCall->args) === 0) {
            if ($argumentFuncCallToMethodCall->getMethodIfNoArgs()) {
                return new MethodCall($propertyFetchNode, $argumentFuncCallToMethodCall->getMethodIfNoArgs());
            }

            return $propertyFetchNode;
        }

        if ($this->isFunctionToMethodCallWithArgs($funcCall, $argumentFuncCallToMethodCall)) {
            return new MethodCall(
                $propertyFetchNode,
                $argumentFuncCallToMethodCall->getMethodIfArgs(),
                $funcCall->args
            );
        }

        return null;
    }

    /**
     * @return PropertyFetch|MethodCall|null
     */
    private function refactorArrayFunctionToMethodCall(
        ArrayFuncCallToMethodCall $arrayFuncCallToMethodCall,
        FuncCall $funcCall,
        Class_ $class
    ): ?Node {
        $propertyName = $this->propertyNaming->fqnToVariableName($arrayFuncCallToMethodCall->getClass());
        $propertyFetch = $this->createPropertyFetch('this', $propertyName);

        $fullyQualifiedObjectType = new FullyQualifiedObjectType($arrayFuncCallToMethodCall->getClass());

        $this->addConstructorDependencyToClass($class, $fullyQualifiedObjectType, $propertyName);

        return $this->createMethodCallArrayFunctionToMethodCall(
            $funcCall,
            $arrayFuncCallToMethodCall,
            $propertyFetch
        );
    }

    private function isFunctionToMethodCallWithArgs(
        FuncCall $funcCall,
        ArgumentFuncCallToMethodCall $argumentFuncCallToMethodCall
    ): bool {
        if ($argumentFuncCallToMethodCall->getMethodIfArgs() === null) {
            return false;
        }

        return count($funcCall->args) >= 1;
    }

    /**
     * @return PropertyFetch|MethodCall|null
     */
    private function createMethodCallArrayFunctionToMethodCall(
        FuncCall $funcCall,
        ArrayFuncCallToMethodCall $arrayFuncCallToMethodCall,
        PropertyFetch $propertyFetch
    ): ?Node {
        if (count($funcCall->args) === 0) {
            return $propertyFetch;
        }

        if ($arrayFuncCallToMethodCall->getArrayMethod() && $this->isArrayType($funcCall->args[0]->value)) {
            return new MethodCall($propertyFetch, $arrayFuncCallToMethodCall->getArrayMethod(), $funcCall->args);
        }

        if ($arrayFuncCallToMethodCall->getNonArrayMethod() && ! $this->isArrayType($funcCall->args[0]->value)) {
            return new MethodCall($propertyFetch, $arrayFuncCallToMethodCall->getNonArrayMethod(), $funcCall->args);
        }

        return null;
    }
}
