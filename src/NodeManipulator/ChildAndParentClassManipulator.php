<?php

declare(strict_types=1);

namespace Rector\Core\NodeManipulator;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\NodeAnalyzer\PromotedPropertyParamCleaner;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

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

    public function __construct(
        NodeFactory $nodeFactory,
        NodeNameResolver $nodeNameResolver,
        NodeRepository $nodeRepository,
        PromotedPropertyParamCleaner $promotedPropertyParamCleaner
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeRepository = $nodeRepository;
        $this->promotedPropertyParamCleaner = $promotedPropertyParamCleaner;
    }

    /**
     * Add "parent::__construct()" where needed
     */
    public function completeParentConstructor(Class_ $class, ClassMethod $classMethod): void
    {
        /** @var string|null $parentClassName */
        $parentClassName = $class->getAttribute(AttributeKey::PARENT_CLASS_NAME);
        if ($parentClassName === null) {
            return;
        }

        // not in analyzed scope, nothing we can do
        $parentClassNode = $this->nodeRepository->findClass($parentClassName);
        if ($parentClassNode !== null) {
            $this->completeParentConstructorBasedOnParentNode($parentClassNode, $classMethod);
            return;
        }

        // complete parent call for __construct()
        if ($parentClassName !== '' && method_exists($parentClassName, MethodName::CONSTRUCT)) {
            $staticCall = $this->nodeFactory->createParentConstructWithParams([]);
            $classMethod->stmts[] = new Expression($staticCall);
        }
    }

    public function completeChildConstructors(Class_ $class, ClassMethod $constructorClassMethod): void
    {
        $className = $this->nodeNameResolver->getName($class);
        if ($className === null) {
            return;
        }

        $childClasses = $this->nodeRepository->findChildrenOfClass($className);

        foreach ($childClasses as $childClass) {
            $childConstructorClassMethod = $childClass->getMethod(MethodName::CONSTRUCT);
            if (! $childConstructorClassMethod instanceof ClassMethod) {
                continue;
            }

            // replicate parent parameters
            $childConstructorClassMethod->params = array_merge(
                $constructorClassMethod->params,
                $childConstructorClassMethod->params
            );

            $parentConstructCallNode = $this->nodeFactory->createParentConstructWithParams(
                $constructorClassMethod->params
            );

            $childConstructorClassMethod->stmts = array_merge(
                [new Expression($parentConstructCallNode)],
                (array) $childConstructorClassMethod->stmts
            );
        }
    }

    private function completeParentConstructorBasedOnParentNode(Class_ $parentClassNode, ClassMethod $classMethod): void
    {
        $firstParentConstructMethodNode = $this->findFirstParentConstructor($parentClassNode);
        if (! $firstParentConstructMethodNode instanceof ClassMethod) {
            return;
        }

        $cleanParams = $this->promotedPropertyParamCleaner->cleanFromFlags($firstParentConstructMethodNode->params);

        // replicate parent parameters
        $classMethod->params = array_merge($cleanParams, $classMethod->params);

        $staticCall = $this->nodeFactory->createParentConstructWithParams($firstParentConstructMethodNode->params);

        $classMethod->stmts[] = new Expression($staticCall);
    }

    private function findFirstParentConstructor(Class_ $class): ?ClassMethod
    {
        while ($class !== null) {
            $constructMethodNode = $class->getMethod(MethodName::CONSTRUCT);
            if ($constructMethodNode !== null) {
                return $constructMethodNode;
            }

            /** @var string|null $parentClassName */
            $parentClassName = $class->getAttribute(AttributeKey::PARENT_CLASS_NAME);
            if ($parentClassName === null) {
                return null;
            }

            $class = $this->nodeRepository->findClass($parentClassName);
        }

        return null;
    }
}
