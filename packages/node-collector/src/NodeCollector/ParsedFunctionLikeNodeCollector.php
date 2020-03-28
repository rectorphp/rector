<?php

declare(strict_types=1);

namespace Rector\NodeCollector\NodeCollector;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

/**
 * All parsed nodes grouped type
 */
final class ParsedFunctionLikeNodeCollector
{
    /**
     * @var ClassMethod[][]
     */
    private $methodsByType = [];

    /**
     * @var Function_[]
     */
    private $functionsByName = [];

    /**
     * @var FuncCall[][]
     */
    private $funcCallsByName = [];

    /**
     * @var MethodCall[][][]|StaticCall[][][]
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

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
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
            $arrayCallableClassAndMethod = $this->matchArrayCallableClassAndMethod($node);
            if ($arrayCallableClassAndMethod === null) {
                return;
            }

            [$className, $methodName] = $arrayCallableClassAndMethod;
            if (! method_exists($className, $methodName)) {
                return;
            }

            $arrayCallable = new ArrayCallable($className, $methodName);
            $this->arrayCallablesByTypeAndMethod[$className][$methodName][] = $arrayCallable;

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

    public function findMethod(string $className, string $methodName): ?ClassMethod
    {
        if (isset($this->methodsByType[$className][$methodName])) {
            return $this->methodsByType[$className][$methodName];
        }

        $parentClass = $className;
        while ($parentClass = get_parent_class($parentClass)) {
            if (isset($this->methodsByType[$parentClass][$methodName])) {
                return $this->methodsByType[$parentClass][$methodName];
            }
        }

        return null;
    }

    public function isFunctionUsed(string $functionName): bool
    {
        return isset($this->funcCallsByName[$functionName]);
    }

    private function addMethod(ClassMethod $classMethod): void
    {
        $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) { // anonymous
            return;
        }

        $methodName = $this->nodeNameResolver->getName($classMethod);
        $this->methodsByType[$className][$methodName] = $classMethod;
    }

    /**
     * @todo decouple to NodeAnalyzer
     * Matches array like: "[$this, 'methodName']" → ['ClassName', 'methodName']
     * @return string[]|null
     */
    private function matchArrayCallableClassAndMethod(Array_ $array): ?array
    {
        if (count($array->items) !== 2) {
            return null;
        }

        if ($array->items[0] === null) {
            return null;
        }

        // $this, self, static, FQN
        if (! $this->isThisVariable($array->items[0]->value)) {
            return null;
        }

        if ($array->items[1] === null) {
            return null;
        }

        if (! $array->items[1]->value instanceof String_) {
            return null;
        }

        /** @var String_ $string */
        $string = $array->items[1]->value;

        $methodName = $string->value;
        $className = $array->getAttribute(AttributeKey::CLASS_NAME);

        if ($className === null) {
            return null;
        }

        return [$className, $methodName];
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

        if ($classType instanceof MixedType) { // anonymous
            return;
        }

        $methodName = $this->nodeNameResolver->getName($node->name);
        if ($methodName === null) {
            return;
        }

        $this->addCallByType($node, $classType, $methodName);
    }

    private function isThisVariable(Node $node): bool
    {
        // $this
        if ($node instanceof Variable && $this->nodeNameResolver->isName($node, 'this')) {
            return true;
        }

        if ($node instanceof ClassConstFetch) {
            if (! $this->nodeNameResolver->isName($node->name, 'class')) {
                return false;
            }

            // self::class, static::class
            if ($this->nodeNameResolver->isNames($node->class, ['self', 'static'])) {
                return true;
            }

            /** @var string|null $className */
            $className = $node->getAttribute(AttributeKey::CLASS_NAME);

            if ($className === null) {
                return false;
            }

            return $this->nodeNameResolver->isName($node->class, $className);
        }

        return false;
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
        if ($classType instanceof ObjectType) {
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
