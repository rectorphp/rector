<?php

declare(strict_types=1);

namespace Rector\Laravel\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Laravel\FunctionToServiceMap;
use Rector\Laravel\ValueObject\ArrayFunctionToMethodCall;
use Rector\Laravel\ValueObject\FunctionToMethodCall;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\FullyQualifiedObjectType;

/**
 * @see https://github.com/laravel/framework/blob/78828bc779e410e03cc6465f002b834eadf160d2/src/Illuminate/Foundation/helpers.php#L959
 * @see https://gist.github.com/barryvdh/bb6ffc5d11e0a75dba67
 *
 * @see \Rector\Laravel\Tests\Rector\FuncCall\HelperFunctionToConstructorInjectionRector\HelperFunctionToConstructorInjectionRectorTest
 */
final class HelperFunctionToConstructorInjectionRector extends AbstractRector
{
    /**
     * @var FunctionToServiceMap
     */
    private $functionToServiceMap;

    public function __construct(FunctionToServiceMap $functionToServiceMap)
    {
        $this->functionToServiceMap = $functionToServiceMap;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Move help facade-like function calls to constructor injection', [
            new CodeSample(
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

        /** @var Class_ $classNode */
        $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);

        $functionName = $this->getName($node);
        if ($functionName === null) {
            return null;
        }

        $functionChange = $this->functionToServiceMap->findByFunction($functionName);
        if ($functionChange === null) {
            return null;
        }

        $objectType = new FullyQualifiedObjectType($functionChange->getClass());
        $this->addPropertyToClass($classNode, $objectType, $functionChange->getProperty());

        $propertyFetchNode = $this->createPropertyFetch('this', $functionChange->getProperty());

        if (count($node->args) === 0) {
            if ($functionChange instanceof FunctionToMethodCall && $functionChange->getMethodIfNoArgs()) {
                return new MethodCall($propertyFetchNode, $functionChange->getMethodIfNoArgs());
            }

            return $propertyFetchNode;
        }

        if ($this->isFunctionToMethodCallWithArgs($node, $functionChange)) {
            /** @var FunctionToMethodCall $functionChange */
            return new MethodCall($propertyFetchNode, $functionChange->getMethodIfArgs(), $node->args);
        }

        if ($functionChange instanceof ArrayFunctionToMethodCall) {
            return $this->createMethodCallArrayFunctionToMethodCall($node, $functionChange, $propertyFetchNode);
        }

        throw new ShouldNotHappenException();
    }

    private function shouldSkipFuncCall(FuncCall $funcCall): bool
    {
        // we can inject only in class context
        $classNode = $funcCall->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classNode instanceof Class_) {
            return true;
        }

        /** @var ClassMethod|null $classMethod */
        $classMethod = $funcCall->getAttribute(AttributeKey::METHOD_NODE);
        if ($classMethod === null) {
            return true;
        }

        return $classMethod->isStatic();
    }

    /**
     * @param FunctionToMethodCall|ArrayFunctionToMethodCall $functionChange
     */
    private function isFunctionToMethodCallWithArgs(Node $node, $functionChange): bool
    {
        if (! $functionChange instanceof FunctionToMethodCall) {
            return false;
        }

        if ($functionChange->getMethodIfArgs() === null) {
            return false;
        }

        return count($node->args) >= 1;
    }

    private function createMethodCallArrayFunctionToMethodCall(
        FuncCall $funcCall,
        ArrayFunctionToMethodCall $arrayFunctionToMethodCall,
        PropertyFetch $propertyFetch
    ) {
        if ($arrayFunctionToMethodCall->getArrayMethod() && $this->isArrayType($funcCall->args[0]->value)) {
            return new MethodCall($propertyFetch, $arrayFunctionToMethodCall->getArrayMethod(), $funcCall->args);
        }

        if ($arrayFunctionToMethodCall->getNonArrayMethod() && ! $this->isArrayType($funcCall->args[0]->value)) {
            return new MethodCall($propertyFetch, $arrayFunctionToMethodCall->getNonArrayMethod(), $funcCall->args);
        }

        return null;
    }
}
