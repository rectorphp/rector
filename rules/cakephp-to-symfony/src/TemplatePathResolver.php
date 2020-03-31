<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Node\Manipulator\PropertyFetchManipulator;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\NodesToRemoveCollector;

final class TemplatePathResolver
{
    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var PropertyFetchManipulator
     */
    private $propertyFetchManipulator;

    /**
     * @var ValueResolver
     */
    private $valueResolver;

    /**
     * @var NodesToRemoveCollector
     */
    private $nodesToRemoveCollector;

    public function __construct(
        CallableNodeTraverser $callableNodeTraverser,
        PropertyFetchManipulator $propertyFetchManipulator,
        ValueResolver $valueResolver,
        NodesToRemoveCollector $nodesToRemoveCollector
    ) {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->propertyFetchManipulator = $propertyFetchManipulator;
        $this->valueResolver = $valueResolver;
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
    }

    public function resolveForClassMethod(ClassMethod $classMethod): string
    {
        $viewPropertyValue = $this->resolveViewPropertyValue($classMethod);
        if ($viewPropertyValue !== null) {
            $viewPropertyValue = Strings::lower($viewPropertyValue);
            return $viewPropertyValue . '.twig';
        }

        $classAndMethodValue = $this->resolveFromClassAndMethod($classMethod);

        return $classAndMethodValue . '.twig';
    }

    public function resolveClassNameTemplatePart(ClassMethod $classMethod): string
    {
        /** @var string $shortClassName */
        $shortClassName = $classMethod->getAttribute(AttributeKey::CLASS_SHORT_NAME);
        $shortClassName = Strings::replace($shortClassName, '#Controller$#i');

        return Strings::lower($shortClassName);
    }

    private function resolveViewPropertyValue(ClassMethod $classMethod): ?string
    {
        $setViewProperty = null;

        $this->callableNodeTraverser->traverseNodesWithCallable($classMethod, function (Node $node) use (
            &$setViewProperty
        ) {
            if (! $node instanceof Assign) {
                return null;
            }

            if (! $this->propertyFetchManipulator->isToThisPropertyFetchOfSpecificNameAssign($node, 'view')) {
                return null;
            }

            $setViewProperty = $node->expr;

            $this->nodesToRemoveCollector->addNodeToRemove($node);
        });

        if ($setViewProperty === null) {
            return null;
        }

        $setViewValue = $this->valueResolver->getValue($setViewProperty);
        if (is_string($setViewValue)) {
            return Strings::lower($setViewValue);
        }

        return null;
    }

    private function resolveFromClassAndMethod(ClassMethod $classMethod): string
    {
        $shortClassName = $this->resolveClassNameTemplatePart($classMethod);
        $methodName = $classMethod->getAttribute(AttributeKey::METHOD_NAME);

        return $shortClassName . '/' . $methodName;
    }
}
