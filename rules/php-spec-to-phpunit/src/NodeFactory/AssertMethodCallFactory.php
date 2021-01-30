<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantBooleanType;
use Rector\Core\PhpParser\Node\Manipulator\ConstFetchManipulator;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

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
        $scope = $expr->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return $name;
        }

        $exprType = $scope->getType($expr);
        if (! $exprType instanceof BooleanType) {
            return $name;
        }

        $isFalse = $exprType instanceof ConstantBooleanType && $exprType->getValue() === false;

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
