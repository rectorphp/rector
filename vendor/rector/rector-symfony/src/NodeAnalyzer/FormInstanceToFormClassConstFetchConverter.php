<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Scalar\String_;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use ReflectionMethod;
final class FormInstanceToFormClassConstFetchConverter
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function processNewInstance(\PhpParser\Node\Expr\MethodCall $methodCall, int $position, int $optionsPosition) : ?\PhpParser\Node
    {
        $args = $methodCall->getArgs();
        if (!isset($args[$position])) {
            return null;
        }
        $argValue = $args[$position]->value;
        $formClassName = $this->resolveFormClassName($argValue);
        if ($formClassName === null) {
            return null;
        }
        if ($argValue instanceof \PhpParser\Node\Expr\New_ && $argValue->args !== []) {
            $methodCall = $this->moveArgumentsToOptions($methodCall, $position, $optionsPosition, $formClassName, $argValue->getArgs());
            if (!$methodCall instanceof \PhpParser\Node\Expr\MethodCall) {
                return null;
            }
        }
        $currentArg = $methodCall->getArgs()[$position];
        $classConstFetch = $this->nodeFactory->createClassConstReference($formClassName);
        $currentArg->value = $classConstFetch;
        return $methodCall;
    }
    /**
     * @param Arg[] $argNodes
     */
    private function moveArgumentsToOptions(\PhpParser\Node\Expr\MethodCall $methodCall, int $position, int $optionsPosition, string $className, array $argNodes) : ?\PhpParser\Node\Expr\MethodCall
    {
        $namesToArgs = $this->resolveNamesToArgs($className, $argNodes);
        // set default data in between
        if ($position + 1 !== $optionsPosition && !isset($methodCall->args[$position + 1])) {
            $methodCall->args[$position + 1] = new \PhpParser\Node\Arg($this->nodeFactory->createNull());
        }
        // @todo decopule and name, so I know what it is
        if (!isset($methodCall->args[$optionsPosition])) {
            $array = new \PhpParser\Node\Expr\Array_();
            foreach ($namesToArgs as $name => $arg) {
                $array->items[] = new \PhpParser\Node\Expr\ArrayItem($arg->value, new \PhpParser\Node\Scalar\String_($name));
            }
            $methodCall->args[$optionsPosition] = new \PhpParser\Node\Arg($array);
        }
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        $formTypeClassReflection = $this->reflectionProvider->getClass($className);
        if (!$formTypeClassReflection->hasConstructor()) {
            return null;
        }
        // nothing we can do, out of scope
        return $methodCall;
    }
    /**
     * @param Arg[] $args
     * @return array<string, Arg>
     */
    private function resolveNamesToArgs(string $className, array $args) : array
    {
        if (!$this->reflectionProvider->hasClass($className)) {
            return [];
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        $reflectionClass = $classReflection->getNativeReflection();
        $constructorReflectionMethod = $reflectionClass->getConstructor();
        if (!$constructorReflectionMethod instanceof \ReflectionMethod) {
            return [];
        }
        $namesToArgs = [];
        foreach ($constructorReflectionMethod->getParameters() as $position => $reflectionParameter) {
            $namesToArgs[$reflectionParameter->getName()] = $args[$position];
        }
        return $namesToArgs;
    }
    private function resolveFormClassName(\PhpParser\Node\Expr $expr) : ?string
    {
        if ($expr instanceof \PhpParser\Node\Expr\New_) {
            // we can only process direct name
            return $this->nodeNameResolver->getName($expr->class);
        }
        $exprType = $this->nodeTypeResolver->getType($expr);
        if ($exprType instanceof \PHPStan\Type\TypeWithClassName) {
            return $exprType->getClassName();
        }
        return null;
    }
}
