<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Manipulator;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeContainer\ParsedNodesByType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\NodeFactory;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class ChildAndParentClassManipulator
{
    /**
     * @var ParsedNodesByType
     */
    private $parsedNodesByType;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(
        ParsedNodesByType $parsedNodesByType,
        NodeFactory $nodeFactory,
        NameResolver $nameResolver
    ) {
        $this->parsedNodesByType = $parsedNodesByType;
        $this->nodeFactory = $nodeFactory;
        $this->nameResolver = $nameResolver;
    }

    public function completeParentConstructor(Class_ $classNode, ClassMethod $classMethod): void
    {
        /** @var string|null $parentClassName */
        $parentClassName = $classNode->getAttribute(AttributeKey::PARENT_CLASS_NAME);
        if ($parentClassName === null) {
            return;
        }

        // not in analyzed scope, nothing we can do
        $parentClassNode = $this->parsedNodesByType->findClass($parentClassName);
        if ($parentClassNode === null) {
            return;
        }

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

    public function completeChildConstructors(Class_ $classNode, ClassMethod $constructorClassMethod): void
    {
        $className = $this->nameResolver->resolve($classNode);
        if ($className === null) {
            return;
        }

        $childClassNodes = $this->parsedNodesByType->findChildrenOfClass($className);

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

            $classNode = $this->parsedNodesByType->findClass($parentClassName);
        }

        return null;
    }
}
