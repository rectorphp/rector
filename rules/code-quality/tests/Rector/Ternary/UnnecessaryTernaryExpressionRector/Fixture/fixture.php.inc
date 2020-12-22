<?php

namespace Rector\CodeQuality\Tests\Rector\Ternary\UnnecessaryTernaryExpressionRector\Fixture;

final class Fixture
{
    public function unnecessaryTernary(): bool
    {
        $baz = $foo === $bar ? true : false;
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

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Ternary\UnnecessaryTernaryExpressionRector\Fixture;

final class Fixture
{
    public function unnecessaryTernary(): bool
    {
        $baz = $foo === $bar;
        return $foo === $bar;
    }

    public function unnecessaryTernaryExpression(): bool
    {
        $return =  $foo === $bar;

        return $return;
    }

    public function unnecessaryTernaryExpressionInverted(): bool
    {
        return $foo < $bar;
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

    public function falsePositiveTernaryExpression(): string
    {
        return $foo === '' ? null : $foo;
    }
}

?>
