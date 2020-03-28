<?php

declare(strict_types=1);

namespace Rector\Privatization\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\TypeWithClassName;
use PHPUnit\Framework\TestCase;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeCollector\NodeFinder\MethodCallParsedNodesFinder;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionMethod;

/**
 * @see \Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector\PrivatizeLocalOnlyMethodRectorTest
 */
final class PrivatizeLocalOnlyMethodRector extends AbstractRector
{
    /**
     * @var MethodCallParsedNodesFinder
     */
    private $methodCallParsedNodesFinder;

    public function __construct(MethodCallParsedNodesFinder $methodCallParsedNodesFinder)
    {
        $this->methodCallParsedNodesFinder = $methodCallParsedNodesFinder;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Privatize local-only use methods', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    /**
     * @api
     */
    public function run()
    {
        return $this->useMe();
    }

    public function useMe()
    {
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    /**
     * @api
     */
    public function run()
    {
        return $this->useMe();
    }

    private function useMe()
    {
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        if ($this->hasExternalCall($node)) {
            return null;
        }

        $this->makePrivate($node);

        return $node;
    }

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        $classNode = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classNode instanceof Class_) {
            return true;
        }

        if ($this->isAnonymousClass($classNode)) {
            return true;
        }

        if ($this->isObjectType($classNode, TestCase::class)) {
            return true;
        }

        if ($this->shouldSkipClassMethod($classMethod)) {
            return true;
        }

        // is interface required method? skip it
        /** @var string $className */
        $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);
        if ($this->isParentLockedMethod($classMethod, $className)) {
            return true;
        }

        if ($this->isChildLockedMethod($classMethod, $className)) {
            return true;
        }

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return false;
        }

        if ($phpDocInfo->hasByName('api')) {
            return true;
        }

        return $phpDocInfo->hasByName('required');
    }

    private function hasExternalCall(ClassMethod $classMethod): bool
    {
        $methodCalls = $this->methodCallParsedNodesFinder->findClassMethodCalls($classMethod);
        $methodName = $this->getName($classMethod);

        if ($this->isArrayCallable($classMethod, $methodCalls, $methodName)) {
            return true;
        }

        // remove static calls and [$this, 'call']
        /** @var MethodCall[] $methodCalls */
        $methodCalls = array_filter($methodCalls, function (object $node) {
            return $node instanceof MethodCall;
        });

        foreach ($methodCalls as $methodCall) {
            $callerType = $this->getStaticType($methodCall->var);
            if (! $callerType instanceof TypeWithClassName) {
                // unable to handle reliably
                return true;
            }

            // external call
            $nodeClassName = $methodCall->getAttribute(AttributeKey::CLASS_NAME);
            if ($nodeClassName !== $callerType->getClassName()) {
                return true;
            }

            // parent class name, must be at least protected
            $methodName = $this->getName($classMethod);
            $reflectionMethod = new ReflectionMethod($nodeClassName, $methodName);
            if ($reflectionMethod->getDeclaringClass()->getName() !== $nodeClassName) {
                return true;
            }
        }

        return false;
    }

    /**
     * @todo move to method visibility vendor lockin
     */
    private function isParentLockedMethod(ClassMethod $classMethod, string $className): bool
    {
        $methodName = $this->getName($classMethod);
        if ($this->isInterfaceMethod($classMethod, $className)) {
            return true;
        }

        return $this->hasParentMethod($className, $methodName);
    }

    /**
     * @todo move to vendor lockin
     */
    private function isInterfaceMethod(ClassMethod $classMethod, string $className): bool
    {
        $interfaceMethods = $this->getInterfaceMethods($className);

        return $this->isNames($classMethod, $interfaceMethods);
    }

    /**
     * @return string[]
     */
    private function getInterfaceMethods(string $className): array
    {
        $interfaces = class_implements($className);

        $interfaceMethods = [];
        foreach ($interfaces as $interface) {
            $interfaceMethods = array_merge($interfaceMethods, get_class_methods($interface));
        }

        return $interfaceMethods;
    }

    private function hasParentMethod(string $className, string $methodName)
    {
        $parentClasses = class_parents($className);

        foreach ($parentClasses as $parentClass) {
            if (! method_exists($parentClass, $methodName)) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * @todo move to vendor lockin
     */
    private function isChildLockedMethod(ClassMethod $classMethod, string $className): bool
    {
        $methodName = $this->getName($classMethod);

        return $this->hasChildMethod($className, $methodName);
    }

    private function shouldSkipClassMethod(ClassMethod $classMethod): bool
    {
        if ($classMethod->isPrivate()) {
            return true;
        }

        if ($classMethod->isAbstract()) {
            return true;
        }

        // skip for now
        if ($classMethod->isStatic()) {
            return true;
        }

        if ($this->isName($classMethod, '__*')) {
            return true;
        }

        // possibly container service factories
        return $this->isNames($classMethod, ['create', 'create*']);
    }

    private function hasChildMethod(string $desiredClassName, string $methodName): bool
    {
        foreach (get_declared_classes() as $className) {
            if ($className === $desiredClassName) {
                continue;
            }

            if (! is_a($className, $desiredClassName, true)) {
                continue;
            }

            if (method_exists($className, $methodName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param StaticCall[]|MethodCall[]|ArrayCallable[] $methodCalls
     */
    private function isArrayCallable(ClassMethod $classMethod, array $methodCalls, ?string $methodName): bool
    {
        /** @var ArrayCallable[] $arrayCallables */
        $arrayCallables = array_filter($methodCalls, function (object $node) {
            return $node instanceof ArrayCallable;
        });

        foreach ($arrayCallables as $arrayCallable) {
            $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);
            if ($className === $arrayCallable->getClass() && $methodName === $arrayCallable->getMethod()) {
                return true;
            }
        }

        return false;
    }
}
