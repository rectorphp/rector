<?php declare(strict_types=1);

final class MyClass
{
    public function unecessaryTernary(): bool
    {
        return $foo === $bar;
    }

    public function unecessaryTernaryExpression(): bool
    {
        $return =  $foo === $bar;

        return $return;
    }
}
