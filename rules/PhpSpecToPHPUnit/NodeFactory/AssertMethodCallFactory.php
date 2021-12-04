<?php

declare (strict_types=1);
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
     * @var bool
     */
    private $isBoolAssert = \false;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
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
    public function __construct(\Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver)
    {
        $this->nodeFactory = $nodeFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->valueResolver = $valueResolver;
    }
    public function createAssertMethod(string $name, \PhpParser\Node\Expr $value, ?\PhpParser\Node\Expr $expected, \PhpParser\Node\Expr\PropertyFetch $testedObjectPropertyFetch) : \PhpParser\Node\Expr\MethodCall
    {
        $this->isBoolAssert = \false;
        // special case with bool!
        if ($expected instanceof \PhpParser\Node\Expr) {
            $name = $this->resolveBoolMethodName($name, $expected);
        }
        $assetMethodCall = $this->nodeFactory->createMethodCall('this', $name);
        if (!$this->isBoolAssert && $expected instanceof \PhpParser\Node\Expr) {
            $assetMethodCall->args[] = new \PhpParser\Node\Arg($this->thisToTestedObjectPropertyFetch($expected, $testedObjectPropertyFetch));
        }
        $assetMethodCall->args[] = new \PhpParser\Node\Arg($this->thisToTestedObjectPropertyFetch($value, $testedObjectPropertyFetch));
        return $assetMethodCall;
    }
    private function resolveBoolMethodName(string $name, \PhpParser\Node\Expr $expr) : string
    {
        if (!$this->valueResolver->isTrueOrFalse($expr)) {
            return $name;
        }
        $isFalse = $this->valueResolver->isFalse($expr);
        if ($name === 'assertSame') {
            $this->isBoolAssert = \true;
            return $isFalse ? 'assertFalse' : 'assertTrue';
        }
        if ($name === 'assertNotSame') {
            $this->isBoolAssert = \true;
            return $isFalse ? 'assertNotFalse' : 'assertNotTrue';
        }
        return $name;
    }
    private function thisToTestedObjectPropertyFetch(\PhpParser\Node\Expr $expr, \PhpParser\Node\Expr\PropertyFetch $propertyFetch) : \PhpParser\Node\Expr
    {
        if (!$expr instanceof \PhpParser\Node\Expr\Variable) {
            return $expr;
        }
        if (!$this->nodeNameResolver->isName($expr, 'this')) {
            return $expr;
        }
        return $propertyFetch;
    }
}
