<?php

declare (strict_types=1);
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
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
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
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\FuncCallManipulator
     */
    private $funcCallManipulator;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\Core\NodeManipulator\FuncCallManipulator $funcCallManipulator)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeComparator = $nodeComparator;
        $this->funcCallManipulator = $funcCallManipulator;
    }
    public function isParameterUsedInClassMethod(\PhpParser\Node\Param $param, \PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        $isUsedDirectly = (bool) $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (\PhpParser\Node $node) use($param) : bool {
            return $this->nodeComparator->areNodesEqual($node, $param->var);
        });
        if ($isUsedDirectly) {
            return \true;
        }
        /** @var FuncCall[] $compactFuncCalls */
        $compactFuncCalls = $this->betterNodeFinder->find((array) $classMethod->stmts, function (\PhpParser\Node $node) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\FuncCall) {
                return \false;
            }
            return $this->nodeNameResolver->isName($node, 'compact');
        });
        $arguments = $this->funcCallManipulator->extractArgumentsFromCompactFuncCalls($compactFuncCalls);
        return $this->nodeNameResolver->isNames($param, $arguments);
    }
    public function isNamedConstructor(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        if (!$this->nodeNameResolver->isName($classMethod, \Rector\Core\ValueObject\MethodName::CONSTRUCT)) {
            return \false;
        }
        $class = $this->betterNodeFinder->findParentType($classMethod, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return \false;
        }
        if ($classMethod->isPrivate()) {
            return \true;
        }
        if ($class->isFinal()) {
            return \false;
        }
        return $classMethod->isProtected();
    }
    public function hasParentMethodOrInterfaceMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod, ?string $methodName = null) : bool
    {
        $methodName = $methodName ?? $this->nodeNameResolver->getName($classMethod->name);
        if ($methodName === null) {
            return \false;
        }
        $scope = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \false;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \false;
        }
        foreach ($classReflection->getParents() as $parentClassReflection) {
            if ($parentClassReflection->hasMethod($methodName)) {
                return \true;
            }
        }
        foreach ($classReflection->getInterfaces() as $interfaceReflection) {
            if ($interfaceReflection->hasMethod($methodName)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param string[] $possibleNames
     */
    public function addMethodParameterIfMissing(\PhpParser\Node $node, \PHPStan\Type\ObjectType $objectType, array $possibleNames) : string
    {
        $classMethod = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\ClassMethod::class);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            // or null?
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        foreach ($classMethod->params as $paramNode) {
            if (!$this->nodeTypeResolver->isObjectType($paramNode, $objectType)) {
                continue;
            }
            return $this->nodeNameResolver->getName($paramNode);
        }
        $paramName = $this->resolveName($classMethod, $possibleNames);
        $classMethod->params[] = new \PhpParser\Node\Param(new \PhpParser\Node\Expr\Variable($paramName), null, new \PhpParser\Node\Name\FullyQualified($objectType->getClassName()));
        return $paramName;
    }
    public function isPropertyPromotion(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        foreach ($classMethod->params as $param) {
            /** @var Param $param */
            if ($param->flags !== 0) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param string[] $possibleNames
     */
    private function resolveName(\PhpParser\Node\Stmt\ClassMethod $classMethod, array $possibleNames) : string
    {
        foreach ($possibleNames as $possibleName) {
            foreach ($classMethod->params as $paramNode) {
                if ($this->nodeNameResolver->isName($paramNode, $possibleName)) {
                    continue 2;
                }
            }
            return $possibleName;
        }
        throw new \Rector\Core\Exception\ShouldNotHappenException();
    }
}
