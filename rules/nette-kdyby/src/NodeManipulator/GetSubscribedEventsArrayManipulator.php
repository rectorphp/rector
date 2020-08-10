<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\NetteKdyby\ValueObject\NetteEventToContributeEventClass;

final class GetSubscribedEventsArrayManipulator
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

    public function change(Array_ $array): void
    {
        $arrayItems = array_filter($array->items, function (ArrayItem $arrayItem): bool {
            return $arrayItem !== null;
        });

        $this->callableNodeTraverser->traverseNodesWithCallable($arrayItems, function (Node $node): ?Node {
            if (! $node instanceof ArrayItem) {
                return null;
            }

            foreach (NetteEventToContributeEventClass::PROPERTY_TO_EVENT_CLASS as $netteEventProperty => $contributeEventClass) {
                if ($node->key === null) {
                    continue;
                }

                if (! $this->valueResolver->isValue($node->key, $netteEventProperty)) {
                    continue;
                }

                $node->key = new ClassConstFetch(new FullyQualified($contributeEventClass), 'class');
            }

            return $node;
        });
    }
}
