<?php

declare(strict_types=1);

namespace Rector\Core\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

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
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var FuncCallManipulator
     */
    private $funcCallManipulator;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        BetterStandardPrinter $betterStandardPrinter,
        FuncCallManipulator $funcCallManipulator,
        NodeNameResolver $nodeNameResolver,
        NodeTypeResolver $nodeTypeResolver
    ) {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->funcCallManipulator = $funcCallManipulator;
    }

    public function isParameterUsedInClassMethod(Param $param, ClassMethod $classMethod): bool
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

            return $this->nodeNameResolver->isName($node, 'compact');
        });

        $arguments = $this->funcCallManipulator->extractArgumentsFromCompactFuncCalls($compactFuncCalls);

        return $this->nodeNameResolver->isNames($param, $arguments);
    }

    public function isNamedConstructor(ClassMethod $classMethod): bool
    {
        if (! $this->nodeNameResolver->isName($classMethod, MethodName::CONSTRUCT)) {
            return false;
        }

        $classLike = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return false;
        }

        return $classMethod->isPrivate() || (! $classLike->isFinal() && $classMethod->isProtected());
    }

    public function hasParentMethodOrInterfaceMethod(ClassMethod $classMethod, ?string $methodName = null): bool
    {
        $methodName = $methodName ?? $this->nodeNameResolver->getName($classMethod->name);

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

        $implementedInterfaces = (array) class_implements($class);

        foreach ($implementedInterfaces as $implementedInterface) {
            /** @var string $implementedInterface */
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

            return $this->nodeNameResolver->isName($node, 'this');
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
            if (! $this->nodeTypeResolver->isObjectType($paramNode, $type)) {
                continue;
            }

            $paramName = $this->nodeNameResolver->getName($paramNode);
            if (! is_string($paramName)) {
                throw new ShouldNotHappenException();
            }

            return $paramName;
        }

        $paramName = $this->resolveName($classMethodNode, $possibleNames);
        $classMethodNode->params[] = new Param(new Variable($paramName), null, new FullyQualified($type));

        return $paramName;
    }

    public function isPropertyPromotion(ClassMethod $classMethod): bool
    {
        foreach ($classMethod->params as $param) {
            /** @var Param $param */
            if ($param->flags !== 0) {
                return true;
            }
        }

        return false;
    }

    private function isMethodInParent(string $class, string $method): bool
    {
        foreach ((array) class_parents($class) as $parentClass) {
            /** @var string $parentClass */
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
                if ($this->nodeNameResolver->isName($paramNode, $possibleName)) {
                    continue 2;
                }
            }

            return $possibleName;
        }

        throw new ShouldNotHappenException();
    }
}
