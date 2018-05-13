<?php declare(strict_types=1);

final class MyClass
{
    public function unnecessaryTernary(): bool
    {
        return $foo === $bar ? true : false;
    }

    public function unnecessaryTernaryExpression(): bool
    {
        $return =  $foo !== $bar
            ? false
            : true;

        return $return;
    }

    public function unnecessaryTernaryExpressionInverted(): bool
    {
        return $foo >= $bar ? false : true;
    }

    public function ternaryExpressionInsideClosure(): bool
    {
        $result = function (): bool {
            return $foo !== 2 ? false : true;
        };

        return $result;
    }

    public function necessaryTernaryExpression(): bool
    {
        return $foo ?: $bar;
    }

    public function falsePositiveTernaryExpression(): string
    {
        return $foo === '' ? null : $foo;
    }
}
