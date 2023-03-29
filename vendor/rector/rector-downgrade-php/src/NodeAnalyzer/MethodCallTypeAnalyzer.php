<?php

declare (strict_types=1);
namespace Rector\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\TypeWithClassName;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class MethodCallTypeAnalyzer
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
    public function isMethodCallTo(MethodCall $methodCall, string $expectedClass, string $expectedMethod) : bool
    {
        if (!$this->isMethodName($methodCall, $expectedMethod)) {
            return \false;
        }
        return $this->isInstanceOf($methodCall->var, $expectedClass);
    }
    private function isMethodName(MethodCall $methodCall, string $expectedName) : bool
    {
        if ($methodCall->name instanceof Identifier && $this->areNamesEqual($methodCall->name->toString(), $expectedName)) {
            return \true;
        }
        $type = $this->nodeTypeResolver->getType($methodCall->name);
        return $type instanceof ConstantStringType && $this->areNamesEqual($type->getValue(), $expectedName);
    }
    private function areNamesEqual(string $left, string $right) : bool
    {
        return \strcasecmp($left, $right) === 0;
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
        if ($this->areNamesEqual($expectedClass, $type->getClassName())) {
            return \true;
        }
        return $type->getAncestorWithClassName($expectedClass) instanceof TypeWithClassName;
    }
}
