<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\Value;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFalseNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantFloatType;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
final class ValueNormalizer
{
    /**
     * @param mixed $value
     * @return bool|float|int|string|mixed[]|\PhpParser\Node\Expr
     */
    public function normalize($value)
    {
        if ($value instanceof \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode) {
            return (int) $value->value;
        }
        if ($value instanceof \PHPStan\Type\Constant\ConstantFloatType || $value instanceof \PHPStan\Type\Constant\ConstantBooleanType) {
            return $value->getValue();
        }
        if ($value instanceof \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode) {
            return \true;
        }
        if ($value instanceof \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFalseNode) {
            return \false;
        }
        if ($value instanceof \Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode) {
            return \array_map(function ($node) {
                return $this->normalize($node);
            }, $value->getValuesWithExplicitSilentAndWithoutQuotes());
        }
        if (\is_string($value) && \strpos($value, '::') !== \false) {
            // class const fetch
            [$class, $constant] = \explode('::', $value);
            return new \PhpParser\Node\Expr\ClassConstFetch(new \PhpParser\Node\Name($class), $constant);
        }
        if ($value instanceof \PHPStan\PhpDocParser\Ast\Node) {
            return (string) $value;
        }
        if (\is_array($value)) {
            return \array_map(function ($item) {
                return $this->normalize($item);
            }, $value);
        }
        return $value;
    }
}
