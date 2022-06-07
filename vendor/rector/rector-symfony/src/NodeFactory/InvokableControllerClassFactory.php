<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeFactory;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Symfony\NodeAnalyzer\InvokableAnalyzer\ActiveClassElementsClassMethodResolver;
use Rector\Symfony\NodeFactory\InvokableController\ActiveClassElementsFilter;
use Rector\Symfony\ValueObject\InvokableController\ActiveClassElements;
final class InvokableControllerClassFactory
{
    /**
     * @readonly
     * @var \Rector\Symfony\NodeFactory\InvokableControllerNameFactory
     */
    private $invokableControllerNameFactory;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\InvokableAnalyzer\ActiveClassElementsClassMethodResolver
     */
    private $activeClassElementsClassMethodResolver;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeFactory\InvokableController\ActiveClassElementsFilter
     */
    private $activeClassElementsFilter;
    public function __construct(\Rector\Symfony\NodeFactory\InvokableControllerNameFactory $invokableControllerNameFactory, NodeNameResolver $nodeNameResolver, ActiveClassElementsClassMethodResolver $activeClassElementsClassMethodResolver, ActiveClassElementsFilter $activeClassElementsFilter)
    {
        $this->invokableControllerNameFactory = $invokableControllerNameFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->activeClassElementsClassMethodResolver = $activeClassElementsClassMethodResolver;
        $this->activeClassElementsFilter = $activeClassElementsFilter;
    }
    public function createWithActionClassMethod(Class_ $class, ClassMethod $actionClassMethod) : Class_
    {
        $controllerName = $this->createControllerName($class, $actionClassMethod);
        $actionClassMethod->name = new Identifier(MethodName::INVOKE);
        $newClass = clone $class;
        $newClassStmts = $this->resolveNewClassStmts($actionClassMethod, $class);
        $newClass->name = new Identifier($controllerName);
        $newClass->stmts = $newClassStmts;
        return $newClass;
    }
    private function createControllerName(Class_ $class, ClassMethod $actionClassMethod) : string
    {
        if (!$class->name instanceof Identifier) {
            throw new ShouldNotHappenException();
        }
        return $this->invokableControllerNameFactory->createControllerName($class->name, $actionClassMethod->name->toString());
    }
    private function filterOutUnusedDependencies(ClassMethod $classMethod, ActiveClassElements $activeClassElements) : ClassMethod
    {
        // to keep original method in other run untouched
        $classMethod = clone $classMethod;
        foreach ($classMethod->params as $key => $param) {
            $paramName = $this->nodeNameResolver->getName($param);
            if (!$activeClassElements->hasPropertyName($paramName)) {
                unset($classMethod->params[$key]);
            }
        }
        $this->filterOutUnusedPropertyAssigns($classMethod, $activeClassElements);
        return $classMethod;
    }
    private function filterOutUnusedPropertyAssigns(ClassMethod $classMethod, ActiveClassElements $activeClassElements) : void
    {
        if (!\is_array($classMethod->stmts)) {
            return;
        }
        foreach ($classMethod->stmts as $key => $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            $stmtExpr = $stmt->expr;
            if (!$stmtExpr instanceof Assign) {
                continue;
            }
            if (!$stmtExpr->var instanceof PropertyFetch) {
                continue;
            }
            $assignPropertyFetch = $stmtExpr->var;
            $propertyFetchName = $this->nodeNameResolver->getName($assignPropertyFetch->name);
            if (!\is_string($propertyFetchName)) {
                continue;
            }
            if ($activeClassElements->hasPropertyName($propertyFetchName)) {
                continue;
            }
            unset($classMethod->stmts[$key]);
        }
    }
    /**
     * @return Stmt[]
     */
    private function resolveNewClassStmts(ClassMethod $actionClassMethod, Class_ $class) : array
    {
        $activeClassElements = $this->activeClassElementsClassMethodResolver->resolve($actionClassMethod);
        $activeClassConsts = $this->activeClassElementsFilter->filterClassConsts($class, $activeClassElements);
        $activeProperties = $this->activeClassElementsFilter->filterProperties($class, $activeClassElements);
        $activeClassMethods = $this->activeClassElementsFilter->filterClassMethod($class, $activeClassElements);
        $newClassStmts = \array_merge($activeClassConsts, $activeProperties);
        foreach ($class->getMethods() as $classMethod) {
            // avoid duplicated names
            if ($this->nodeNameResolver->isName($classMethod->name, MethodName::CONSTRUCT)) {
                $classMethod = $this->filterOutUnusedDependencies($classMethod, $activeClassElements);
                $newClassStmts[] = $classMethod;
            }
        }
        $newClassStmts[] = $actionClassMethod;
        return \array_merge($newClassStmts, $activeClassMethods);
    }
}
