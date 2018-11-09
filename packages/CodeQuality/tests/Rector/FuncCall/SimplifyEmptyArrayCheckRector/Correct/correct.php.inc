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

    public function functionCallInsideEmpty($values): bool
    {
        return array_filter($values) === [];
    }
}
