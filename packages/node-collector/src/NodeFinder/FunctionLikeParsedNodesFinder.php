<?php

declare(strict_types=1);

namespace Rector\NodeCollector\NodeFinder;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\TypeWithClassName;
use Rector\NodeCollector\NodeCollector\ParsedFunctionLikeNodeCollector;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;

final class FunctionLikeParsedNodesFinder
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var ParsedFunctionLikeNodeCollector
     */
    private $parsedFunctionLikeNodeCollector;

    /**
     * @var TypeUnwrapper
     */
    private $typeUnwrapper;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        NodeTypeResolver $nodeTypeResolver,
        ParsedFunctionLikeNodeCollector $parsedFunctionLikeNodeCollector,
        TypeUnwrapper $typeUnwrapper
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->parsedFunctionLikeNodeCollector = $parsedFunctionLikeNodeCollector;
        $this->typeUnwrapper = $typeUnwrapper;
    }

    public function findClassMethodByMethodCall(MethodCall $methodCall): ?ClassMethod
    {
        $className = $this->resolveCallerClassName($methodCall);
        if ($className === null) {
            return null;
        }

        $methodName = $this->nodeNameResolver->getName($methodCall->name);
        if ($methodName === null) {
            return null;
        }

        return $this->findClassMethod($methodName, $className);
    }

    public function findClassMethodByStaticCall(StaticCall $staticCall): ?ClassMethod
    {
        $methodName = $this->nodeNameResolver->getName($staticCall->name);
        if ($methodName === null) {
            return null;
        }

        $objectType = $this->nodeTypeResolver->resolve($staticCall->class);

        $classNames = TypeUtils::getDirectClassNames($objectType);
        foreach ($classNames as $className) {
            $foundMethod = $this->findClassMethod($methodName, $className);
            if ($foundMethod !== null) {
                return $foundMethod;
            }
        }

        return null;
    }

    public function findFunction(string $name): ?Function_
    {
        return $this->parsedFunctionLikeNodeCollector->findFunction($name);
    }

    public function findClassMethod(string $methodName, string $className): ?ClassMethod
    {
        return $this->parsedFunctionLikeNodeCollector->findMethod($className, $methodName);
    }

    /**
     * @return MethodCall[][]|StaticCall[][]
     */
    public function findMethodCallsOnClass(string $className): array
    {
        return $this->parsedFunctionLikeNodeCollector->findMethodCallsOnClass($className);
    }

    private function resolveCallerClassName(MethodCall $methodCall): ?string
    {
        $callerType = $this->nodeTypeResolver->getStaticType($methodCall->var);
        $callerObjectType = $this->typeUnwrapper->unwrapFirstObjectTypeFromUnionType($callerType);
        if (! $callerObjectType instanceof TypeWithClassName) {
            return null;
        }

        return $callerObjectType->getClassName();
    }
}
