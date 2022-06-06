<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Nette\Kdyby\NodeManipulator;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayItem;
use RectorPrefix20220606\PhpParser\Node\Expr\ClassConstFetch;
use RectorPrefix20220606\PhpParser\Node\Name\FullyQualified;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\Value\ValueResolver;
use RectorPrefix20220606\Rector\Nette\Kdyby\ValueObject\NetteEventToContributeEventClass;
use RectorPrefix20220606\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class GetSubscribedEventsArrayManipulator
{
    /**
     * @readonly
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser, ValueResolver $valueResolver)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->valueResolver = $valueResolver;
    }
    public function change(Array_ $array) : void
    {
        $arrayItems = \array_filter($array->items, function ($arrayItem) : bool {
            return $arrayItem !== null;
        });
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($arrayItems, function (Node $node) : ?Node {
            if (!$node instanceof ArrayItem) {
                return null;
            }
            foreach (NetteEventToContributeEventClass::PROPERTY_TO_EVENT_CLASS as $netteEventProperty => $contributeEventClass) {
                if ($node->key === null) {
                    continue;
                }
                if (!$this->valueResolver->isValue($node->key, $netteEventProperty)) {
                    continue;
                }
                $node->key = new ClassConstFetch(new FullyQualified($contributeEventClass), 'class');
            }
            return $node;
        });
    }
}
