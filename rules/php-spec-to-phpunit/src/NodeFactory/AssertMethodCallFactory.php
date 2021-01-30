<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NodeNameResolver\NodeNameResolver;

final class AssertMethodCallFactory
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var bool
     */
    private $isBoolAssert = false;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ValueResolver
     */
    private $valueResolver;

    public function __construct(
        NodeFactory $nodeFactory,
        NodeNameResolver $nodeNameResolver,
        ValueResolver $valueResolver
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->valueResolver = $valueResolver;
    }

    public function createAssertMethod(
        string $name,
        Expr $value,
        ?Expr $expected,
        PropertyFetch $testedObjectPropertyFetch
    ): MethodCall {
        $this->isBoolAssert = false;

        // special case with bool!
        if ($expected !== null) {
            $name = $this->resolveBoolMethodName($name, $expected);
        }

        $assetMethodCall = $this->nodeFactory->createMethodCall('this', $name);

        if (! $this->isBoolAssert && $expected) {
            $assetMethodCall->args[] = new Arg($this->thisToTestedObjectPropertyFetch(
                $expected,
                $testedObjectPropertyFetch
            ));
        }

        $assetMethodCall->args[] = new Arg($this->thisToTestedObjectPropertyFetch($value, $testedObjectPropertyFetch));

        return $assetMethodCall;
    }

    private function resolveBoolMethodName(string $name, Expr $expr): string
    {
        if (! $this->valueResolver->isTrueOrFalse($expr)) {
            return $name;
        }

        $isFalse = $this->valueResolver->isFalse($expr);
        if ($name === 'assertSame') {
            $this->isBoolAssert = true;
            return $isFalse ? 'assertFalse' : 'assertTrue';
        }

        if ($name === 'assertNotSame') {
            $this->isBoolAssert = true;
            return $isFalse ? 'assertNotFalse' : 'assertNotTrue';
        }

        return $name;
    }

    private function thisToTestedObjectPropertyFetch(Expr $expr, PropertyFetch $propertyFetch): Expr
    {
        if (! $expr instanceof Variable) {
            return $expr;
        }

        if (! $this->nodeNameResolver->isName($expr, 'this')) {
            return $expr;
        }

        return $propertyFetch;
    }
}
