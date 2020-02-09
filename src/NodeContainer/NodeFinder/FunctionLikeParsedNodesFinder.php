<?php

declare(strict_types=1);

namespace Rector\Core\NodeContainer\NodeFinder;

use Nette\Utils\Strings;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\TypeUtils;
use Rector\Core\NodeContainer\NodeCollector\ParsedFunctionLikeNodeCollector;
use Rector\Core\PhpParser\Node\Resolver\NameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use ReflectionClass;

final class FunctionLikeParsedNodesFinder
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var ParsedFunctionLikeNodeCollector
     */
    private $parsedFunctionLikeNodeCollector;

    public function __construct(
        NameResolver $nameResolver,
        NodeTypeResolver $nodeTypeResolver,
        ParsedFunctionLikeNodeCollector $parsedFunctionLikeNodeCollector
    ) {
        $this->nameResolver = $nameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->parsedFunctionLikeNodeCollector = $parsedFunctionLikeNodeCollector;
    }

    public function findClassMethodByMethodCall(MethodCall $methodCall): ?ClassMethod
    {
        /** @var string|null $className */
        $className = $methodCall->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return null;
        }

        $methodName = $this->nameResolver->getName($methodCall->name);
        if ($methodName === null) {
            return null;
        }

        return $this->findMethod($methodName, $className);
    }

    public function findClassMethodByStaticCall(StaticCall $staticCall): ?ClassMethod
    {
        $methodName = $this->nameResolver->getName($staticCall->name);
        if ($methodName === null) {
            return null;
        }

        $objectType = $this->nodeTypeResolver->resolve($staticCall->class);

        $classNames = TypeUtils::getDirectClassNames($objectType);
        foreach ($classNames as $className) {
            $foundMethod = $this->findMethod($methodName, $className);
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

    public function findMethod(string $methodName, string $className): ?ClassMethod
    {
        return $this->parsedFunctionLikeNodeCollector->findMethod($className, $methodName);
    }

    /**
     * @todo deocpule
     */
    public function isStaticMethod(string $methodName, string $className): bool
    {
        $methodNode = $this->findMethod($methodName, $className);
        if ($methodNode !== null) {
            return $methodNode->isStatic();
        }

        // could be static in doc type magic
        // @see https://regex101.com/r/tlvfTB/1
        if (class_exists($className) || trait_exists($className)) {
            $reflectionClass = new ReflectionClass($className);
            if (Strings::match(
                (string) $reflectionClass->getDocComment(),
                '#@method\s*static\s*(.*?)\b' . $methodName . '\b#'
            )) {
                return true;
            }

            // probably magic method → we don't know
            if (! method_exists($className, $methodName)) {
                return false;
            }

            $methodReflection = $reflectionClass->getMethod($methodName);
            return $methodReflection->isStatic();
        }

        return false;
    }

    /**
     * @return MethodCall[]|StaticCall[]|Array_[]
     */
    public function findClassMethodCalls(ClassMethod $classMethod): array
    {
        /** @var string|null $className */
        $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) { // anonymous
            return [];
        }

        /** @var string|null $methodName */
        $methodName = $this->nameResolver->getName($classMethod);
        if ($methodName === null) {
            return [];
        }

        return $this->parsedFunctionLikeNodeCollector->findByClassAndMethod($className, $methodName);
    }

    /**
     * @return MethodCall[][]|StaticCall[][]
     */
    public function findMethodCallsOnClass(string $className): array
    {
        return $this->parsedFunctionLikeNodeCollector->findMethodCallsOnClass($className);
    }
}
