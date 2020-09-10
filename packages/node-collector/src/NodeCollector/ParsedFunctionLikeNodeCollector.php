<?php

declare(strict_types=1);

namespace Rector\NodeCollector\NodeCollector;

use Nette\Utils\Arrays;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeCollector\NodeAnalyzer\ArrayCallableMethodReferenceAnalyzer;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

/**
 * All parsed nodes grouped type
 * @todo add ParsedNodesRepository, merge with all other node collectors to smymplfiy the access
 *
 * Have api like:
 * - findXByX for N
 * - getXByX for 1
 */
final class ParsedFunctionLikeNodeCollector
{
    /**
     * @var array<string, ClassMethod[]>
     */
    private $classMethodsByType = [];

    /**
     * @var array<string, Function_>
     */
    private $functionsByName = [];

    /**
     * @var array<string, FuncCall[]>
     */
    private $funcCallsByName = [];

    /**
     * @var array<string, array<array<MethodCall|StaticCall>>>
     */
    private $methodsCallsByTypeAndMethod = [];

    /**
     * E.g. [$this, 'someLocalMethod']
     * @var ArrayCallable[][][]
     */
    private $arrayCallablesByTypeAndMethod = [];

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var ArrayCallableMethodReferenceAnalyzer
     */
    private $arrayCallableMethodReferenceAnalyzer;

    public function __construct(
        ArrayCallableMethodReferenceAnalyzer $arrayCallableMethodReferenceAnalyzer,
        NodeNameResolver $nodeNameResolver
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->arrayCallableMethodReferenceAnalyzer = $arrayCallableMethodReferenceAnalyzer;
    }

    /**
     * To prevent circular reference
     * @required
     */
    public function autowireParsedNodesByType(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function collect(Node $node): void
    {
        if ($node instanceof ClassMethod) {
            $this->addMethod($node);
            return;
        }

        // array callable - [$this, 'someCall']
        if ($node instanceof Array_) {
            $arrayCallable = $this->arrayCallableMethodReferenceAnalyzer->match($node);
            if ($arrayCallable === null) {
                return;
            }

            if (! $arrayCallable->isExistingMethod()) {
                return;
            }

            $this->arrayCallablesByTypeAndMethod[$arrayCallable->getClass()][$arrayCallable->getMethod()][] = $arrayCallable;

            return;
        }

        if ($node instanceof MethodCall || $node instanceof StaticCall) {
            $this->addCall($node);
        }

        if ($node instanceof Function_) {
            $functionName = $this->nodeNameResolver->getName($node);
            $this->functionsByName[$functionName] = $node;
        }

        if ($node instanceof FuncCall) {
            $functionName = $this->nodeNameResolver->getName($node);
            $this->funcCallsByName[$functionName][] = $node;
        }
    }

    public function findFunction(string $name): ?Function_
    {
        return $this->functionsByName[$name] ?? null;
    }

    /**
     * @return MethodCall[][]|StaticCall[][]
     */
    public function findMethodCallsOnClass(string $className): array
    {
        return $this->methodsCallsByTypeAndMethod[$className] ?? [];
    }

    /**
     * @return MethodCall[]|StaticCall[]|ArrayCallable[]
     */
    public function findByClassAndMethod(string $className, string $methodName): array
    {
        return $this->methodsCallsByTypeAndMethod[$className][$methodName] ?? $this->arrayCallablesByTypeAndMethod[$className][$methodName] ?? [];
    }

    /**
     * @return StaticCall[]
     */
    public function findStaticCallsByClassMethod(ClassMethod $classMethod): array
    {
        $calls = $this->findCallsByClassMethod($classMethod);
        return array_filter($calls, function (Node $node): bool {
            return $node instanceof StaticCall;
        });
    }

    /**
     * @return ClassMethod[]
     */
    public function findClassMethodByTypeAndMethod(string $desiredType, string $desiredMethodName): array
    {
        $classMethods = [];

        foreach ($this->classMethodsByType as $className => $classMethodByMethodName) {
            if (! is_a($className, $desiredType, true)) {
                continue;
            }

            if (! isset($classMethodByMethodName[$desiredMethodName])) {
                continue;
            }

            $classMethods[] = $classMethodByMethodName[$desiredMethodName];
        }

        return $classMethods;
    }

    public function findClassMethodByStaticCall(StaticCall $staticCall): ?ClassMethod
    {
        $staticCallType = $this->nodeTypeResolver->resolve($staticCall->class);
        if ($staticCallType instanceof TypeWithClassName) {
            $staticCallClass = $staticCallType->getClassName();
        } else {
            // possible union type?
            return null;
        }

        if ($staticCallClass === null) {
            return null;
        }

        $method = $this->nodeNameResolver->getName($staticCall->name);
        if ($method === null) {
            return null;
        }

        return $this->findMethod($staticCallClass, $method);
    }

    public function findMethod(string $className, string $methodName): ?ClassMethod
    {
        if (isset($this->classMethodsByType[$className][$methodName])) {
            return $this->classMethodsByType[$className][$methodName];
        }

        $parentClass = $className;
        while ($parentClass = get_parent_class($parentClass)) {
            if (isset($this->classMethodsByType[$parentClass][$methodName])) {
                return $this->classMethodsByType[$parentClass][$methodName];
            }
        }

        return null;
    }

    public function isFunctionUsed(string $functionName): bool
    {
        return isset($this->funcCallsByName[$functionName]);
    }

    /**
     * @return MethodCall[]
     */
    public function getMethodsCalls(): array
    {
        $calls = Arrays::flatten($this->methodsCallsByTypeAndMethod);

        return array_filter($calls, function (Node $node): bool {
            return $node instanceof MethodCall;
        });
    }

    public function findClassMethodByMethodReflection(MethodReflection $methodReflection): ?ClassMethod
    {
        $methodName = $methodReflection->getName();

        /** @var string $className */
        $className = $methodReflection->getDeclaringClass()->getName();
        return $this->findMethod($className, $methodName);
    }

    private function addMethod(ClassMethod $classMethod): void
    {
        $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);
        // anonymous
        if ($className === null) {
            return;
        }

        $methodName = $this->nodeNameResolver->getName($classMethod);
        $this->classMethodsByType[$className][$methodName] = $classMethod;
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function addCall(Node $node): void
    {
        // one node can be of multiple-class types
        if ($node instanceof MethodCall) {
            $classType = $this->resolveNodeClassTypes($node->var);
        } else {
            /** @var StaticCall $node */
            $classType = $this->resolveNodeClassTypes($node->class);
        }

        // anonymous
        if ($classType instanceof MixedType) {
            return;
        }

        $methodName = $this->nodeNameResolver->getName($node->name);
        if ($methodName === null) {
            return;
        }

        $this->addCallByType($node, $classType, $methodName);
    }
    /**
     * @return MethodCall[]|StaticCall[]|ArrayCallable[]
     */
    private function findCallsByClassMethod(ClassMethod $classMethod): array
    {
        $class = $classMethod->getAttribute(AttributeKey::CLASS_NAME);
        if (! is_string($class)) {
            throw new ShouldNotHappenException();
        }

        /** @var string $method */
        $method = $this->nodeNameResolver->getName($classMethod->name);
        return $this->findByClassAndMethod($class, $method);
    }

    private function resolveNodeClassTypes(Node $node): Type
    {
        if ($node instanceof MethodCall && $node->var instanceof Variable && $node->var->name === 'this') {
            /** @var string|null $className */
            $className = $node->getAttribute(AttributeKey::CLASS_NAME);

            if ($className) {
                return new ObjectType($className);
            }

            return new MixedType();
        }

        if ($node instanceof MethodCall) {
            return $this->nodeTypeResolver->resolve($node->var);
        }

        return $this->nodeTypeResolver->resolve($node);
    }

    private function addCallByType(Node $node, Type $classType, string $methodName): void
    {
        if ($classType instanceof TypeWithClassName) {
            $this->methodsCallsByTypeAndMethod[$classType->getClassName()][$methodName][] = $node;
        }

        if ($classType instanceof UnionType) {
            foreach ($classType->getTypes() as $unionedType) {
                if (! $unionedType instanceof ObjectType) {
                    continue;
                }

                $this->methodsCallsByTypeAndMethod[$unionedType->getClassName()][$methodName][] = $node;
            }
        }
    }
}
