<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\Manipulator;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\NodeContainer\NodeCollector\ParsedNodeCollector;
use Rector\Core\NodeContainer\NodeFinder\ClassLikeParsedNodesFinder;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\PhpParser\Node\Resolver\NameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ChildAndParentClassManipulator
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var NameResolver
     */
    private $nameResolver;

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
        NameResolver $nameResolver,
        ClassLikeParsedNodesFinder $classLikeParsedNodesFinder
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->nameResolver = $nameResolver;
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
        if ($parentClassName !== '' && method_exists($parentClassName, '__construct')) {
            $parentConstructCallNode = $this->nodeFactory->createParentConstructWithParams([]);
            $classMethod->stmts[] = new Expression($parentConstructCallNode);
            return;
        }
    }

    public function completeChildConstructors(Class_ $classNode, ClassMethod $constructorClassMethod): void
    {
        $className = $this->nameResolver->getName($classNode);
        if ($className === null) {
            return;
        }

        $childClassNodes = $this->classLikeParsedNodesFinder->findChildrenOfClass($className);

        foreach ($childClassNodes as $childClassNode) {
            if ($childClassNode->getMethod('__construct') === null) {
                continue;
            }

            /** @var ClassMethod $childClassConstructorMethodNode */
            $childClassConstructorMethodNode = $childClassNode->getMethod('__construct');

            // replicate parent parameters
            $childClassConstructorMethodNode->params = array_merge(
                $constructorClassMethod->params,
                $childClassConstructorMethodNode->params
            );

            $parentConstructCallNode = $this->nodeFactory->createParentConstructWithParams(
                $constructorClassMethod->params
            );

            $childClassConstructorMethodNode->stmts = array_merge(
                [new Expression($parentConstructCallNode)],
                (array) $childClassConstructorMethodNode->stmts
            );
        }
    }

    private function completeParentConstructorBasedOnParentNode(Class_ $parentClassNode, ClassMethod $classMethod): void
    {
        // iterate up?
        $firstParentConstructMethodNode = $this->findFirstParentConstructor($parentClassNode);
        if ($firstParentConstructMethodNode === null) {
            return;
        }

        if ($firstParentConstructMethodNode->params === []) {
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
            $constructMethodNode = $classNode->getMethod('__construct');
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
