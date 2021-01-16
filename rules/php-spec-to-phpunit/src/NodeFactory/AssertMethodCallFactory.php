<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\PhpParser\Node\Manipulator\ConstFetchManipulator;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeNameResolver\NodeNameResolver;

final class AssertMethodCallFactory
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var ConstFetchManipulator
     */
    private $constFetchManipulator;

    /**
     * @var bool
     */
    private $isBoolAssert = false;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(
        NodeFactory $nodeFactory,
        ConstFetchManipulator $constFetchManipulator,
        NodeNameResolver $nodeNameResolver
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->constFetchManipulator = $constFetchManipulator;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function createAssertMethod(
        string $name,
        Expr $value,
        ?Expr $expected,
        Expr\PropertyFetch $testedObjectPropertyFetch
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
        if (! $this->constFetchManipulator->isBool($expr)) {
            return $name;
        }

        if ($name === 'assertSame') {
            $this->isBoolAssert = true;
            return $this->constFetchManipulator->isFalse($expr) ? 'assertFalse' : 'assertTrue';
        }

        if ($name === 'assertNotSame') {
            $this->isBoolAssert = true;
            return $this->constFetchManipulator->isFalse($expr) ? 'assertNotFalse' : 'assertNotTrue';
        }

        return $name;
    }

    private function thisToTestedObjectPropertyFetch(Expr $expr, Expr\PropertyFetch $propertyFetch): Expr
    {
        if (! $expr instanceof Expr\Variable) {
            return $expr;
        }

        if (! $this->nodeNameResolver->isName($expr, 'this')) {
            return $expr;
        }

        return $propertyFetch;
    }
}
