<?php declare(strict_types=1);

final class MyClass
{
    public function unnecessaryIfElse(): bool
    {
        return $something === 1;
    }

    public function superfluousIfElseStruct(): bool
    {
        $foo = isset($bar);

        return $foo >= true;
    }

    public function unnecessaryIf(): bool
    {
        return 1 === true;
    }
}
