<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\Resolver\NameResolver;
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
     * @var FunctionLikeManipulator
     */
    private $functionLikeManipulator;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        BetterStandardPrinter $betterStandardPrinter,
        NodeTypeResolver $nodeTypeResolver,
        FunctionLikeManipulator $functionLikeManipulator,
        NameResolver $nameResolver
    ) {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->functionLikeManipulator = $functionLikeManipulator;
        $this->nameResolver = $nameResolver;
    }

    public function isParameterUsedMethod(Param $param, ClassMethod $classMethod): bool
    {
        return (bool) $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (Node $node) use (
            $param
        ) {
            return $this->betterStandardPrinter->areNodesEqual($node, $param->var);
        });
    }

    public function hasParentMethodOrInterfaceMethod(ClassMethod $classMethod): bool
    {
        $class = $classMethod->getAttribute(Attribute::CLASS_NAME);
        if (! is_string($class)) {
            return false;
        }

        $method = $classMethod->getAttribute(Attribute::METHOD_NAME);
        if (! is_string($method)) {
            return false;
        }

        if (! class_exists($class)) {
            return false;
        }

        if ($this->isMethodInParent($class, $method)) {
            return true;
        }

        $implementedInterfaces = class_implements($class);
        foreach ($implementedInterfaces as $implementedInterface) {
            if (method_exists($implementedInterface, $method)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string[]
     */
    public function resolveReturnType(ClassMethod $classMethod): array
    {
        if ($classMethod->returnType !== null) {
            return $this->nodeTypeResolver->resolve($classMethod->returnType);
        }

        $staticReturnType = $this->functionLikeManipulator->resolveStaticReturnTypeInfo($classMethod);
        if ($staticReturnType === null) {
            return [];
        }

        $getFqnTypeNode = $staticReturnType->getFqnTypeNode();
        if ($getFqnTypeNode === null) {
            return [];
        }

        $fqnTypeName = $this->nameResolver->resolve($getFqnTypeNode);
        if ($fqnTypeName === null) {
            return [];
        }

        return [$fqnTypeName];
    }

    /**
     * Is method actually static, or has some $this-> calls?
     */
    public function isStaticClassMethod(ClassMethod $classMethod): bool
    {
        return (bool) $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (Node $node) {
            if (! $node instanceof Variable) {
                return false;
            }

            return $this->nameResolver->isName($node, 'this');
        });
    }

    /**
     * @return MethodCall[]
     */
    public function getAllClassMethodCall(ClassMethod $classMethod): array
    {
        $classNode = $classMethod->getAttribute(Attribute::CLASS_NODE);
        if ($classNode === null) {
            return [];
        }

        return $this->betterNodeFinder->find($classNode, function (Node $node) use ($classMethod) {
            // itself
            if ($this->betterStandardPrinter->areNodesEqual($node, $classMethod)) {
                return false;
            }

            // is it the name match?
            if ($this->nameResolver->resolve($node) !== $this->nameResolver->resolve($classMethod)) {
                return false;
            }

            if ($node instanceof MethodCall && $this->nameResolver->isName($node->var, 'this')) {
                return true;
            }
            return $node instanceof StaticCall && $this->nameResolver->isNames($node->class, ['self', 'static']);
        });
    }

    /**
     * @param string[] $possibleNames
     */
    public function addMethodParameterIfMissing(Node $node, string $type, array $possibleNames): string
    {
        $classMethodNode = $node->getAttribute(Attribute::METHOD_NODE);
        if (! $classMethodNode instanceof ClassMethod) {
            // or null?
            throw new ShouldNotHappenException();
        }

        foreach ($classMethodNode->params as $paramNode) {
            if ($this->nodeTypeResolver->isType($paramNode, $type)) {
                return $this->nameResolver->resolve($paramNode);
            }
        }

        $paramName = $this->resolveName($classMethodNode, $possibleNames);
        $classMethodNode->params[] = new Param(new Variable($paramName), null, new FullyQualified($type));

        return $paramName;
    }

    private function isMethodInParent(string $class, string $method): bool
    {
        $parentClass = $class;

        while ($parentClass = get_parent_class($parentClass)) {
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
}
