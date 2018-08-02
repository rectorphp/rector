<?php declare(strict_types=1);

final class MyClass
{
    public function unnecessaryIfElse(): bool
    {
        if ($something === 1) {
            return true;
        } else {
            return false;
        }
    }

    public function superfluousIfElseStruct(): bool
    {
        $foo = isset($bar);

        if ($foo >= true) {
            return true;
        } else {
            return false;
        }
    }
}
