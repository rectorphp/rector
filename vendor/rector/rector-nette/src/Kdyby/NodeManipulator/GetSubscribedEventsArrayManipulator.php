<?php

declare (strict_types=1);
namespace Rector\Nette\Kdyby\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Nette\Kdyby\ValueObject\NetteEventToContributeEventClass;
use RectorPrefix20211020\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class GetSubscribedEventsArrayManipulator
{
    /**
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(\RectorPrefix20211020\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->valueResolver = $valueResolver;
    }
    public function change(\PhpParser\Node\Expr\Array_ $array) : void
    {
        $arrayItems = \array_filter($array->items, function (\PhpParser\Node\Expr\ArrayItem $arrayItem) : bool {
            return $arrayItem !== null;
        });
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($arrayItems, function (\PhpParser\Node $node) : ?Node {
            if (!$node instanceof \PhpParser\Node\Expr\ArrayItem) {
                return null;
            }
            foreach (\Rector\Nette\Kdyby\ValueObject\NetteEventToContributeEventClass::PROPERTY_TO_EVENT_CLASS as $netteEventProperty => $contributeEventClass) {
                if ($node->key === null) {
                    continue;
                }
                if (!$this->valueResolver->isValue($node->key, $netteEventProperty)) {
                    continue;
                }
                $node->key = new \PhpParser\Node\Expr\ClassConstFetch(new \PhpParser\Node\Name\FullyQualified($contributeEventClass), 'class');
            }
            return $node;
        });
    }
}
