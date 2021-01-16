<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NetteKdyby\ValueObject\NetteEventToContributeEventClass;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class GetSubscribedEventsArrayManipulator
{
    /**
     * @var SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;

    /**
     * @var ValueResolver
     */
    private $valueResolver;

    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser, ValueResolver $valueResolver)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->valueResolver = $valueResolver;
    }

    public function change(Array_ $array): void
    {
        $arrayItems = array_filter($array->items, function (ArrayItem $arrayItem): bool {
            return $arrayItem !== null;
        });

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($arrayItems, function (Node $node): ?Node {
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
