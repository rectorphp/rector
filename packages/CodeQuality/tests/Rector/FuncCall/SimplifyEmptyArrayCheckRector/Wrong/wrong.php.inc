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

    public function functionCallInsideEmpty($values): bool
    {
        return is_array($values) && empty(array_filter($values));
    }
}
