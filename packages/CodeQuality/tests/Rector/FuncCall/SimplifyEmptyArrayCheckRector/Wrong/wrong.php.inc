<?php declare(strict_types=1);

final class MyClass
{
    public function isEmptyArray($values): bool
    {
        return is_array($values) && empty($values);
    }

    public function isEmptyArray2($values): bool
    {
        return empty($values) && is_array($values);
    }

    public function isntEmptyArray($values): bool
    {
        return is_array($values) && ! empty($values);
    }

    public function isntEmptyArray2($values): bool
    {
        return ! empty($values) && is_array($values);
    }

    public function twoNegatives($values): bool
    {
        return ! empty($values->foo) && ! is_array($values->bar);
    }

    public function functionCallInsideEmpty($values): bool
    {
        return is_array($values) && empty(array_filter($values));
    }
}
