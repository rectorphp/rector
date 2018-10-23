<?php declare(strict_types=1);

final class MyClass
{
    public function isEmptyArray($values): bool
    {
        return $values === [];
    }

    public function isEmptyArray2($values): bool
    {
        return $values === [];
    }

    public function isntEmptyArray($values): bool
    {
        return $values !== [];
    }

    public function isntEmptyArray2($values): bool
    {
        return $values !== [];
    }

    public function twoNegatives($values): bool
    {
        return ! empty($values->foo) && ! is_array($values->bar);
    }

    public function functionCallInsideEmpty($values): bool
    {
        return array_filter($values) === [];
    }
}
