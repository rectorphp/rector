<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer\FormType;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\PhpParser\Node\NodeFactory;
use ReflectionMethod;
final class CreateFormTypeOptionsArgMover
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
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @param Arg[] $argNodes
     */
    public function moveArgumentsToOptions(\PhpParser\Node\Expr\MethodCall $methodCall, int $position, int $optionsPosition, string $className, array $argNodes) : ?\PhpParser\Node\Expr\MethodCall
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
        $nativeReflection = $classReflection->getNativeReflection();
        $constructorReflectionMethod = $nativeReflection->getConstructor();
        if (!$constructorReflectionMethod instanceof \ReflectionMethod) {
            return [];
        }
        return $this->createArgsByName($constructorReflectionMethod, $args);
    }
    /**
     * @param Arg[] $args
     * @return array<string, Arg>
     */
    private function createArgsByName(\ReflectionMethod $constructorReflectionMethod, array $args) : array
    {
        $namesToArgs = [];
        foreach ($constructorReflectionMethod->getParameters() as $position => $reflectionParameter) {
            $namesToArgs[$reflectionParameter->getName()] = $args[$position];
        }
        return $namesToArgs;
    }
}
