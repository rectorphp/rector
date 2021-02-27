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
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Reflection\ReflectionProvider;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionParameter;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;

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

    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    /**
     * @var PrivatesAccessor
     */
    private $privatesAccessor;

    public function __construct(
        NodeRepository $nodeRepository,
        NodeNameResolver $nodeNameResolver,
        ReflectionProvider $reflectionProvider,
        PrivatesAccessor $privatesAccessor
    ) {
        $this->nodeRepository = $nodeRepository;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->privatesAccessor = $privatesAccessor;
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
        $function = $this->nodeRepository->findFunction($functionName);
        if ($function instanceof Function_) {
            return $this->resolveFromFunctionLike($function);
        }

        // non existing function
        $functionNameNode = new Name($functionName);
        if (! $this->reflectionProvider->hasFunction($functionNameNode, null)) {
            return [];
        }

        $functionReflection = $this->reflectionProvider->getFunction($functionNameNode, null);
        if ($functionReflection->isBuiltin()) {
            return [];
        }

        $defaultValues = [];

        $parametersAcceptor = $functionReflection->getVariants()[0];

        foreach ($parametersAcceptor->getParameters() as $key => $reflectionParameter) {
            /** @var ReflectionParameter $nativeReflectionParameter */
            $nativeReflectionParameter = $this->privatesAccessor->getPrivateProperty(
                $reflectionParameter,
                'reflection'
            );
            if (! $nativeReflectionParameter->isDefaultValueAvailable()) {
                continue;
            }

            $defaultValues[$key] = BuilderHelpers::normalizeValue($nativeReflectionParameter->getDefaultValue());
        }

        return $defaultValues;
    }
}
