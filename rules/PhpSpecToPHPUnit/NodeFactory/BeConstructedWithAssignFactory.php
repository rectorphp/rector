<?php

declare (strict_types=1);
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
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->valueResolver = $valueResolver;
        $this->nodeFactory = $nodeFactory;
    }
    public function create(\PhpParser\Node\Expr\MethodCall $methodCall, string $testedClass, \PhpParser\Node\Expr\PropertyFetch $propertyFetch) : ?\PhpParser\Node\Expr\Assign
    {
        if ($this->nodeNameResolver->isName($methodCall->name, 'beConstructedWith')) {
            $new = new \PhpParser\Node\Expr\New_(new \PhpParser\Node\Name\FullyQualified($testedClass));
            $new->args = $methodCall->args;
            return new \PhpParser\Node\Expr\Assign($propertyFetch, $new);
        }
        if ($this->nodeNameResolver->isName($methodCall->name, 'beConstructedThrough')) {
            if (!isset($methodCall->args[0])) {
                return null;
            }
            if (!$methodCall->args[0] instanceof \PhpParser\Node\Arg) {
                return null;
            }
            $methodName = $this->valueResolver->getValue($methodCall->args[0]->value);
            $staticCall = $this->nodeFactory->createStaticCall($testedClass, $methodName);
            $this->moveConstructorArguments($methodCall, $staticCall);
            return new \PhpParser\Node\Expr\Assign($propertyFetch, $staticCall);
        }
        return null;
    }
    private function moveConstructorArguments(\PhpParser\Node\Expr\MethodCall $methodCall, \PhpParser\Node\Expr\StaticCall $staticCall) : void
    {
        if (!isset($methodCall->args[1])) {
            return;
        }
        if (!$methodCall->args[1] instanceof \PhpParser\Node\Arg) {
            return;
        }
        if (!$methodCall->args[1]->value instanceof \PhpParser\Node\Expr\Array_) {
            return;
        }
        /** @var Array_ $array */
        $array = $methodCall->args[1]->value;
        foreach ($array->items as $arrayItem) {
            if (!$arrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            $staticCall->args[] = new \PhpParser\Node\Arg($arrayItem->value);
        }
    }
}
