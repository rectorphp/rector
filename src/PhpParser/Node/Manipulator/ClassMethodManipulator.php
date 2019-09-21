<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PhpParser\Printer\BetterStandardPrinter;

final class ClassMethodManipulator
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var ValueResolver
     */
    private $valueResolver;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        BetterStandardPrinter $betterStandardPrinter,
        NodeTypeResolver $nodeTypeResolver,
        NameResolver $nameResolver,
        ValueResolver $valueResolver
    ) {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nameResolver = $nameResolver;
        $this->valueResolver = $valueResolver;
    }

    public function isParameterUsedMethod(Param $param, ClassMethod $classMethod): bool
    {
        $isUsedDirectly = (bool) $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (Node $node) use (
            $param
        ): bool {
            return $this->betterStandardPrinter->areNodesEqual($node, $param->var);
        });

        if ($isUsedDirectly) {
            return true;
        }

        /** @var FuncCall[] $compactFuncCalls */
        $compactFuncCalls = $this->betterNodeFinder->find((array) $classMethod->stmts, function (Node $node): bool {
            if (! $node instanceof FuncCall) {
                return false;
            }

            return $this->nameResolver->isName($node, 'compact');
        });

        $arguments = $this->extractArgumentsFromCompactFuncCalls($compactFuncCalls);

        return $this->nameResolver->isNames($param, $arguments);
    }

    public function hasParentMethodOrInterfaceMethod(ClassMethod $classMethod, ?string $methodName = null): bool
    {
        $methodName = $methodName ?? $this->nameResolver->getName($classMethod->name);

        $class = $classMethod->getAttribute(AttributeKey::CLASS_NAME);
        if (! is_string($class)) {
            return false;
        }

        if (! class_exists($class)) {
            return false;
        }

        if (! is_string($methodName)) {
            return false;
        }

        if ($this->isMethodInParent($class, $methodName)) {
            return true;
        }

        $implementedInterfaces = class_implements($class);
        foreach ($implementedInterfaces as $implementedInterface) {
            if (method_exists($implementedInterface, $methodName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Is method actually static, or has some $this-> calls?
     */
    public function isStaticClassMethod(ClassMethod $classMethod): bool
    {
        return (bool) $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (Node $node): bool {
            if (! $node instanceof Variable) {
                return false;
            }

            return $this->nameResolver->isName($node, 'this');
        });
    }

    /**
     * @param string[] $possibleNames
     */
    public function addMethodParameterIfMissing(Node $node, string $type, array $possibleNames): string
    {
        $classMethodNode = $node->getAttribute(AttributeKey::METHOD_NODE);
        if (! $classMethodNode instanceof ClassMethod) {
            // or null?
            throw new ShouldNotHappenException();
        }

        foreach ($classMethodNode->params as $paramNode) {
            if ($this->nodeTypeResolver->isObjectType($paramNode, $type)) {
                return $this->nameResolver->getName($paramNode);
            }
        }

        $paramName = $this->resolveName($classMethodNode, $possibleNames);
        $classMethodNode->params[] = new Param(new Variable($paramName), null, new FullyQualified($type));

        return $paramName;
    }

    private function isMethodInParent(string $class, string $method): bool
    {
        foreach (class_parents($class) as $parentClass) {
            if (method_exists($parentClass, $method)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string[] $possibleNames
     */
    private function resolveName(ClassMethod $classMethod, array $possibleNames): string
    {
        foreach ($possibleNames as $possibleName) {
            foreach ($classMethod->params as $paramNode) {
                if ($this->nameResolver->isName($paramNode, $possibleName)) {
                    continue 2;
                }
            }

            return $possibleName;
        }

        throw new ShouldNotHappenException();
    }

    /**
     * @param FuncCall[] $compactFuncCalls
     * @return string[]
     */
    private function extractArgumentsFromCompactFuncCalls(array $compactFuncCalls): array
    {
        $arguments = [];
        foreach ($compactFuncCalls as $compactFuncCall) {
            foreach ($compactFuncCall->args as $arg) {
                $value = $this->valueResolver->resolve($arg->value);

                if ($value) {
                    $arguments[] = $value;
                }
            }
        }

        return $arguments;
    }
}
