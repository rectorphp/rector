<?php declare(strict_types=1);

final class MyClass
{
    public function unnecessaryTernary(): bool
    {
        return $foo === $bar;
    }

    public function unnecessaryTernaryExpression(): bool
    {
        $return =  $foo === $bar;

        return $return;
    }

    public function unnecessaryTernaryExpressionInverted(): bool
    {
        return $foo <= $bar;
    }

    public function ternaryExpressionInsideClosure(): bool
    {
        $result = function (): bool {
            return $foo === 2;
        };

        return $result;
    }

    public function necessaryTernaryExpression(): bool
    {
        return $foo ?: $bar;
    }
}
