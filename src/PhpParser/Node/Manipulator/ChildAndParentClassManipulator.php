<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\Manipulator;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeCollector\NodeCollector\ParsedNodeCollector;
use Rector\NodeCollector\NodeFinder\ClassLikeParsedNodesFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ChildAndParentClassManipulator
{
    /**
     * @var string
     */
    private const CONSTRUCT = '__construct';

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ClassLikeParsedNodesFinder
     */
    private $classLikeParsedNodesFinder;

    /**
     * @var ParsedNodeCollector
     */
    private $parsedNodeCollector;

    public function __construct(
        ParsedNodeCollector $parsedNodeCollector,
        NodeFactory $nodeFactory,
        NodeNameResolver $nodeNameResolver,
        ClassLikeParsedNodesFinder $classLikeParsedNodesFinder
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->classLikeParsedNodesFinder = $classLikeParsedNodesFinder;
        $this->parsedNodeCollector = $parsedNodeCollector;
    }

    /**
     * Add "parent::__construct()" where needed
     */
    public function completeParentConstructor(Class_ $classNode, ClassMethod $classMethod): void
    {
        /** @var string|null $parentClassName */
        $parentClassName = $classNode->getAttribute(AttributeKey::PARENT_CLASS_NAME);
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
        if ($parentClassName !== '' && method_exists($parentClassName, self::CONSTRUCT)) {
            $parentConstructCallNode = $this->nodeFactory->createParentConstructWithParams([]);
            $classMethod->stmts[] = new Expression($parentConstructCallNode);
        }
    }

    public function completeChildConstructors(Class_ $classNode, ClassMethod $constructorClassMethod): void
    {
        $className = $this->nodeNameResolver->getName($classNode);
        if ($className === null) {
            return;
        }

        $childClassNodes = $this->classLikeParsedNodesFinder->findChildrenOfClass($className);

        foreach ($childClassNodes as $childClassNode) {
            $childConstructorClassMethod = $childClassNode->getMethod(self::CONSTRUCT);
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

        $parentConstructCallNode = $this->nodeFactory->createParentConstructWithParams(
            $firstParentConstructMethodNode->params
        );

        $classMethod->stmts[] = new Expression($parentConstructCallNode);
    }

    private function findFirstParentConstructor(Class_ $classNode): ?ClassMethod
    {
        while ($classNode !== null) {
            $constructMethodNode = $classNode->getMethod(self::CONSTRUCT);
            if ($constructMethodNode !== null) {
                return $constructMethodNode;
            }

            /** @var string|null $parentClassName */
            $parentClassName = $classNode->getAttribute(AttributeKey::PARENT_CLASS_NAME);
            if ($parentClassName === null) {
                return null;
            }

            $classNode = $this->parsedNodeCollector->findClass($parentClassName);
        }

        return null;
    }
}
