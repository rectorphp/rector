<?php

declare(strict_types=1);

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
     * @return array<mixed>
     */
    public function normalize($value): bool | float | int | string | array | Expr
    {
        if ($value instanceof ConstExprIntegerNode) {
            return (int) $value->value;
        }

        if ($value instanceof ConstantFloatType || $value instanceof ConstantBooleanType) {
            return $value->getValue();
        }

        if ($value instanceof ConstExprTrueNode) {
            return true;
        }

        if ($value instanceof ConstExprFalseNode) {
            return false;
        }

        if ($value instanceof CurlyListNode) {
            return array_map(
                fn ($node) => $this->normalize($node),
                $value->getValuesWithExplicitSilentAndWithoutQuotes()
            );
        }

        if (is_string($value) && str_contains($value, '::')) {
            // class const fetch
            [$class, $constant] = explode('::', $value);
            return new ClassConstFetch(new Name($class), $constant);
        }

        if ($value instanceof Node) {
            return (string) $value;
        }

        if (\is_array($value)) {
            return array_map(fn ($item) => $this->normalize($item), $value);
        }

        return $value;
    }
}
