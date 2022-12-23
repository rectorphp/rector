<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\TypeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\TypeWithClassName;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class MethodTypeAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @api downgrade
     * @param class-string $expectedClass
     * @param non-empty-string $expectedMethod
     */
    public function isCallTo(MethodCall $methodCall, string $expectedClass, string $expectedMethod) : bool
    {
        if (!$this->isMethodName($methodCall, $expectedMethod)) {
            return \false;
        }
        return $this->isInstanceOf($methodCall->var, $expectedClass);
    }
    /**
     * @param non-empty-string $expectedName
     */
    private function isMethodName(MethodCall $methodCall, string $expectedName) : bool
    {
        if ($methodCall->name instanceof Identifier && $this->areMethodNamesEqual($methodCall->name->toString(), $expectedName)) {
            return \true;
        }
        $type = $this->nodeTypeResolver->getType($methodCall->name);
        return $type instanceof ConstantStringType && $this->areMethodNamesEqual($type->getValue(), $expectedName);
    }
    private function areMethodNamesEqual(string $left, string $right) : bool
    {
        $comparison = \strcasecmp($left, $right);
        return $comparison === 0;
    }
    /**
     * @param class-string $expectedClass
     */
    private function isInstanceOf(Expr $expr, string $expectedClass) : bool
    {
        $type = $this->nodeTypeResolver->getType($expr);
        if (!$type instanceof TypeWithClassName) {
            return \false;
        }
        if ($this->areClassNamesEqual($expectedClass, $type->getClassName())) {
            return \true;
        }
        return $type->getAncestorWithClassName($expectedClass) !== null;
    }
    private function areClassNamesEqual(string $left, string $right) : bool
    {
        $comparison = \strcasecmp($left, $right);
        return $comparison === 0;
    }
}
