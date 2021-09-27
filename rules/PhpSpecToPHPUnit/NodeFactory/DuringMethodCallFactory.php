<?php

declare (strict_types=1);
namespace Rector\PhpSpecToPHPUnit\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\PostRector\Collector\NodesToAddCollector;
final class DuringMethodCallFactory
{
    /**
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @var \Rector\PostRector\Collector\NodesToAddCollector
     */
    private $nodesToAddCollector;
    public function __construct(\Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver, \Rector\PostRector\Collector\NodesToAddCollector $nodesToAddCollector)
    {
        $this->valueResolver = $valueResolver;
        $this->nodesToAddCollector = $nodesToAddCollector;
    }
    public function create(\PhpParser\Node\Expr\MethodCall $methodCall, \PhpParser\Node\Expr\PropertyFetch $propertyFetch) : \PhpParser\Node\Expr\MethodCall
    {
        if (!isset($methodCall->args[0])) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        if (!$methodCall->args[0] instanceof \PhpParser\Node\Arg) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $name = $this->valueResolver->getValue($methodCall->args[0]->value);
        $thisObjectPropertyMethodCall = new \PhpParser\Node\Expr\MethodCall($propertyFetch, $name);
        if (isset($methodCall->args[1]) && $methodCall->args[1] instanceof \PhpParser\Node\Arg && $methodCall->args[1]->value instanceof \PhpParser\Node\Expr\Array_) {
            /** @var Array_ $array */
            $array = $methodCall->args[1]->value;
            if (isset($array->items[0])) {
                $thisObjectPropertyMethodCall->args[] = new \PhpParser\Node\Arg($array->items[0]->value);
            }
        }
        /** @var MethodCall $parentMethodCall */
        $parentMethodCall = $methodCall->var;
        $parentMethodCall->name = new \PhpParser\Node\Identifier('expectException');
        // add $this->object->someCall($withArgs)
        $this->nodesToAddCollector->addNodeAfterNode($thisObjectPropertyMethodCall, $methodCall);
        return $parentMethodCall;
    }
}
