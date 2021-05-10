<?php

declare (strict_types=1);
namespace Rector\Core\NodeManipulator;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\NodeAnalyzer\PromotedPropertyParamCleaner;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver;
use Rector\NodeNameResolver\NodeNameResolver;
final class ChildAndParentClassManipulator
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var NodeRepository
     */
    private $nodeRepository;
    /**
     * @var PromotedPropertyParamCleaner
     */
    private $promotedPropertyParamCleaner;
    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var ParentClassScopeResolver
     */
    private $parentClassScopeResolver;
    public function __construct(\Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeCollector\NodeCollector\NodeRepository $nodeRepository, \Rector\Core\NodeAnalyzer\PromotedPropertyParamCleaner $promotedPropertyParamCleaner, \PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\NodeCollector\ScopeResolver\ParentClassScopeResolver $parentClassScopeResolver)
    {
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeRepository = $nodeRepository;
        $this->promotedPropertyParamCleaner = $promotedPropertyParamCleaner;
        $this->reflectionProvider = $reflectionProvider;
        $this->parentClassScopeResolver = $parentClassScopeResolver;
    }
    /**
     * Add "parent::__construct()" where needed
     */
    public function completeParentConstructor(\PhpParser\Node\Stmt\Class_ $class, \PhpParser\Node\Stmt\ClassMethod $classMethod) : void
    {
        $className = $this->nodeNameResolver->getName($class);
        if ($className === null) {
            return;
        }
        if (!$this->reflectionProvider->hasClass($className)) {
            return;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        $parentClassReflection = $classReflection->getParentClass();
        if ($parentClassReflection === \false) {
            return;
        }
        // not in analyzed scope, nothing we can do
        $parentClassNode = $this->nodeRepository->findClass($parentClassReflection->getName());
        if ($parentClassNode instanceof \PhpParser\Node\Stmt\Class_) {
            $this->completeParentConstructorBasedOnParentNode($parentClassNode, $classMethod);
            return;
        }
        // complete parent call for __construct()
        if ($parentClassReflection->hasMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT)) {
            $staticCall = $this->nodeFactory->createParentConstructWithParams([]);
            $classMethod->stmts[] = new \PhpParser\Node\Stmt\Expression($staticCall);
        }
    }
    public function completeChildConstructors(\PhpParser\Node\Stmt\Class_ $class, \PhpParser\Node\Stmt\ClassMethod $constructorClassMethod) : void
    {
        $className = $this->nodeNameResolver->getName($class);
        if ($className === null) {
            return;
        }
        $childClasses = $this->nodeRepository->findChildrenOfClass($className);
        foreach ($childClasses as $childClass) {
            $childConstructorClassMethod = $childClass->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
            if (!$childConstructorClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
                continue;
            }
            // replicate parent parameters
            $childConstructorClassMethod->params = \array_merge($constructorClassMethod->params, $childConstructorClassMethod->params);
            $parentConstructCallNode = $this->nodeFactory->createParentConstructWithParams($constructorClassMethod->params);
            $childConstructorClassMethod->stmts = \array_merge([new \PhpParser\Node\Stmt\Expression($parentConstructCallNode)], (array) $childConstructorClassMethod->stmts);
        }
    }
    private function completeParentConstructorBasedOnParentNode(\PhpParser\Node\Stmt\Class_ $parentClassNode, \PhpParser\Node\Stmt\ClassMethod $classMethod) : void
    {
        $firstParentConstructMethodNode = $this->findFirstParentConstructor($parentClassNode);
        if (!$firstParentConstructMethodNode instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return;
        }
        $cleanParams = $this->promotedPropertyParamCleaner->cleanFromFlags($firstParentConstructMethodNode->params);
        // replicate parent parameters
        $classMethod->params = \array_merge($cleanParams, $classMethod->params);
        $staticCall = $this->nodeFactory->createParentConstructWithParams($firstParentConstructMethodNode->params);
        $classMethod->stmts[] = new \PhpParser\Node\Stmt\Expression($staticCall);
    }
    private function findFirstParentConstructor(\PhpParser\Node\Stmt\Class_ $class) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        while ($class !== null) {
            $constructMethodNode = $class->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
            if ($constructMethodNode !== null) {
                return $constructMethodNode;
            }
            $parentClassName = $this->parentClassScopeResolver->resolveParentClassName($class);
            if ($parentClassName === null) {
                return null;
            }
            $class = $this->nodeRepository->findClass($parentClassName);
        }
        return null;
    }
}
