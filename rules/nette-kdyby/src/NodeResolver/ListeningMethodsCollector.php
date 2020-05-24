<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\NodeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\NetteKdyby\ValueObject\NetteEventToContributeEventClass;

final class ListeningMethodsCollector
{
    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var ValueResolver
     */
    private $valueResolver;

    public function __construct(CallableNodeTraverser $callableNodeTraverser, ValueResolver $valueResolver)
    {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->valueResolver = $valueResolver;
    }

    /**
     * @return array<string, ClassMethod>
     */
    public function collectFromClassAndGetSubscribedEventClassMethod(Class_ $class, ClassMethod $classMethod): array
    {
        $classMethodsByEventClass = [];

        $this->callableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use (
            $class,
            &$classMethodsByEventClass
        ) {
            if (! $node instanceof ArrayItem) {
                return null;
            }

            $possibleMethodName = $this->valueResolver->getValue($node->value);
            if (! is_string($possibleMethodName)) {
                return null;
            }

            $classMethod = $class->getMethod($possibleMethodName);
            if ($classMethod === null) {
                return null;
            }

            if ($node->key === null) {
                return null;
            }

            $eventClass = $this->valueResolver->getValue($node->key);

            $contributeEventClasses = NetteEventToContributeEventClass::PROPERTY_TO_EVENT_CLASS;
            if (! in_array($eventClass, $contributeEventClasses, true)) {
                return null;
            }

            $classMethodsByEventClass[$eventClass] = $classMethod;
        });

        return $classMethodsByEventClass;
    }
}
