<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Rector;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Commander\NodeRemovingCommander;
use Rector\PhpParser\Node\Manipulator\PropertyFetchManipulator;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;

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
     * @var ClassNaming
     */
    private $classNaming;

    /**
     * @var NodeRemovingCommander
     */
    private $nodeRemovingCommander;

    public function __construct(
        CallableNodeTraverser $callableNodeTraverser,
        PropertyFetchManipulator $propertyFetchManipulator,
        ValueResolver $valueResolver,
        ClassNaming $classNaming,
        NodeRemovingCommander $nodeRemovingCommander
    ) {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->propertyFetchManipulator = $propertyFetchManipulator;
        $this->valueResolver = $valueResolver;
        $this->classNaming = $classNaming;
        $this->nodeRemovingCommander = $nodeRemovingCommander;
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
        /** @var string $className */
        $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);
        $shortClassName = $this->classNaming->getShortName($className);

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

            $this->nodeRemovingCommander->addNode($node);
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
