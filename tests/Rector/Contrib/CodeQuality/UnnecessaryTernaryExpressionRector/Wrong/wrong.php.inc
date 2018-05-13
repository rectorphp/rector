<?php declare(strict_types=1);

final class MyClass
{
    public function unecessaryTernary(): bool
    {
        return $foo === $bar ? true : false;
    }
}
