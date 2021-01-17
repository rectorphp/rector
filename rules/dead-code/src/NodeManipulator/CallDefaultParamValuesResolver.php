<?php

declare(strict_types=1);

namespace Rector\DeadCode\NodeManipulator;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionFunction;

final class CallDefaultParamValuesResolver
{
    /**
     * @var NodeRepository
     */
    private $nodeRepository;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeRepository $nodeRepository, NodeNameResolver $nodeNameResolver)
    {
        $this->nodeRepository = $nodeRepository;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @param Function_|ClassMethod $functionLike
     * @return Node[]
     */
    public function resolveFromFunctionLike(FunctionLike $functionLike): array
    {
        $defaultValues = [];
        foreach ($functionLike->getParams() as $key => $param) {
            if ($param->default === null) {
                continue;
            }

            $defaultValues[$key] = $param->default;
        }

        return $defaultValues;
    }

    /**
     * @param StaticCall|FuncCall|MethodCall $node
     * @return Node[]
     */
    public function resolveFromCall(Node $node): array
    {
        $nodeName = $this->nodeNameResolver->getName($node->name);
        if ($nodeName === null) {
            return [];
        }

        if ($node instanceof FuncCall) {
            return $this->resolveFromFunctionName($nodeName);
        }

        /** @var string|null $className */
        $className = $node->getAttribute(AttributeKey::CLASS_NAME);
        // anonymous class
        if ($className === null) {
            return [];
        }

        $classMethodNode = $this->nodeRepository->findClassMethod($className, $nodeName);
        if ($classMethodNode !== null) {
            return $this->resolveFromFunctionLike($classMethodNode);
        }

        return [];
    }

    /**
     * @return Node[]|Expr[]
     */
    private function resolveFromFunctionName(string $functionName): array
    {
        $functionNode = $this->nodeRepository->findFunction($functionName);
        if ($functionNode !== null) {
            return $this->resolveFromFunctionLike($functionNode);
        }

        // non existing function
        if (! function_exists($functionName)) {
            return [];
        }

        $reflectionFunction = new ReflectionFunction($functionName);
        if ($reflectionFunction->isUserDefined()) {
            $defaultValues = [];

            foreach ($reflectionFunction->getParameters() as $key => $reflectionParameter) {
                if ($reflectionParameter->isDefaultValueAvailable()) {
                    $defaultValues[$key] = BuilderHelpers::normalizeValue($reflectionParameter->getDefaultValue());
                }
            }

            return $defaultValues;
        }

        return [];
    }
}
