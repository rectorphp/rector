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
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class ClassMethodManipulator
{
    public function __construct(
        private BetterNodeFinder $betterNodeFinder,
        private NodeNameResolver $nodeNameResolver,
        private NodeTypeResolver $nodeTypeResolver,
        private NodeComparator $nodeComparator,
        private FuncCallManipulator $funcCallManipulator
    ) {
    }

    public function isParameterUsedInClassMethod(Param $param, ClassMethod $classMethod): bool
    {
        $isUsedDirectly = (bool) $this->betterNodeFinder->findFirst(
            (array) $classMethod->stmts,
            fn (Node $node): bool => $this->nodeComparator->areNodesEqual($node, $param->var)
        );

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
        if ($classMethod->isPrivate()) {
            return true;
        }
        if ($classLike->isFinal()) {
            return false;
        }
        return $classMethod->isProtected();
    }

    public function hasParentMethodOrInterfaceMethod(ClassMethod $classMethod, ?string $methodName = null): bool
    {
        $methodName = $methodName ?? $this->nodeNameResolver->getName($classMethod->name);
        if ($methodName === null) {
            return false;
        }

        $scope = $classMethod->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return false;
        }

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return false;
        }

        foreach ($classReflection->getParents() as $parentClassReflection) {
            if ($parentClassReflection->hasMethod($methodName)) {
                return true;
            }
        }

        foreach ($classReflection->getInterfaces() as $interfaceReflection) {
            if ($interfaceReflection->hasMethod($methodName)) {
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
    public function addMethodParameterIfMissing(Node $node, ObjectType $objectType, array $possibleNames): string
    {
        $classMethodNode = $node->getAttribute(AttributeKey::METHOD_NODE);
        if (! $classMethodNode instanceof ClassMethod) {
            // or null?
            throw new ShouldNotHappenException();
        }

        foreach ($classMethodNode->params as $paramNode) {
            if (! $this->nodeTypeResolver->isObjectType($paramNode, $objectType)) {
                continue;
            }

            $paramName = $this->nodeNameResolver->getName($paramNode);
            if (! is_string($paramName)) {
                throw new ShouldNotHappenException();
            }

            return $paramName;
        }

        $paramName = $this->resolveName($classMethodNode, $possibleNames);
        $classMethodNode->params[] = new Param(new Variable($paramName), null, new FullyQualified(
            $objectType->getClassName()
        ));

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
