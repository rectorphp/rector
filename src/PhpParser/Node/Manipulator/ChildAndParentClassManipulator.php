<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\Manipulator;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeCollector\NodeCollector\ParsedNodeCollector;
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
     * @var ParsedNodeCollector
     */
    private $parsedNodeCollector;

    /**
     * @var NodeRepository
     */
    private $nodeRepository;

    public function __construct(
        NodeFactory $nodeFactory,
        NodeNameResolver $nodeNameResolver,
        ParsedNodeCollector $parsedNodeCollector,
        NodeRepository $nodeRepository
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->parsedNodeCollector = $parsedNodeCollector;
        $this->nodeRepository = $nodeRepository;
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
        $parentClassNode = $this->parsedNodeCollector->findClass($parentClassName);
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

        $childClassNodes = $this->nodeRepository->findChildrenOfClass($className);

        foreach ($childClassNodes as $childClassNode) {
            $childConstructorClassMethod = $childClassNode->getMethod(MethodName::CONSTRUCT);
            if ($childConstructorClassMethod === null) {
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
        if ($firstParentConstructMethodNode === null) {
            return;
        }

        // replicate parent parameters
        $classMethod->params = array_merge($firstParentConstructMethodNode->params, $classMethod->params);

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

            $class = $this->parsedNodeCollector->findClass($parentClassName);
        }

        return null;
    }
}
