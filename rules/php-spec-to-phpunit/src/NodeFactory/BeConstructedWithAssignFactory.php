<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NodeNameResolver\NodeNameResolver;

final class BeConstructedWithAssignFactory
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ValueResolver
     */
    private $valueResolver;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(NodeNameResolver $nodeNameResolver,
    ValueResolver $valueResolver,
    NodeFactory $nodeFactory)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->valueResolver = $valueResolver;
        $this->nodeFactory = $nodeFactory;
    }

    public function create(MethodCall $methodCall, string $testedClass, PropertyFetch $propertyFetch): ?Assign
    {
        if ($this->nodeNameResolver->isName($methodCall->name, 'beConstructedWith')) {
            $new = new New_(new FullyQualified($testedClass));
            $new->args = $methodCall->args;

            return new Assign($propertyFetch, $new);
        }

        if ($this->nodeNameResolver->isName($methodCall->name, 'beConstructedThrough')) {
            $methodName = $this->valueResolver->getValue($methodCall->args[0]->value);
            $staticCall = $this->nodeFactory->createStaticCall($testedClass, $methodName);

            $this->moveConstructorArguments($methodCall, $staticCall);

            return new Assign($propertyFetch, $staticCall);
        }

        return null;
    }

    private function moveConstructorArguments(MethodCall $methodCall, StaticCall $staticCall): void
    {
        if (! isset($methodCall->args[1])) {
            return;
        }

        if (! $methodCall->args[1]->value instanceof Array_) {
            return;
        }

        /** @var Array_ $array */
        $array = $methodCall->args[1]->value;
        foreach ($array->items as $arrayItem) {
            if (! $arrayItem instanceof ArrayItem) {
                continue;
            }

            $staticCall->args[] = new Arg($arrayItem->value);
        }
    }
}
