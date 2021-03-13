<?php

declare(strict_types=1);

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
     * @var ValueResolver
     */
    private $valueResolver;

    /**
     * @var NodesToAddCollector
     */
    private $nodesToAddCollector;

    public function __construct(ValueResolver $valueResolver, NodesToAddCollector $nodesToAddCollector)
    {
        $this->valueResolver = $valueResolver;
        $this->nodesToAddCollector = $nodesToAddCollector;
    }

    public function create(MethodCall $methodCall, PropertyFetch $propertyFetch): MethodCall
    {
        if (! isset($methodCall->args[0])) {
            throw new ShouldNotHappenException();
        }

        $name = $this->valueResolver->getValue($methodCall->args[0]->value);
        $thisObjectPropertyMethodCall = new MethodCall($propertyFetch, $name);

        if (isset($methodCall->args[1]) && $methodCall->args[1]->value instanceof Array_) {
            /** @var Array_ $array */
            $array = $methodCall->args[1]->value;

            if (isset($array->items[0])) {
                $thisObjectPropertyMethodCall->args[] = new Arg($array->items[0]->value);
            }
        }

        /** @var MethodCall $parentMethodCall */
        $parentMethodCall = $methodCall->var;
        $parentMethodCall->name = new Identifier('expectException');

        // add $this->object->someCall($withArgs)
        $this->nodesToAddCollector->addNodeAfterNode($thisObjectPropertyMethodCall, $methodCall);

        return $parentMethodCall;
    }
}
