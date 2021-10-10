<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

/** @return array<array<mixed>>|null */
function foo1(int $a, float $b): ?array
{
    $bar = null;

    if ($a > 0) {
        for ($i = 0; $i < $a; $i++) {
            $bar[] = [
                'a' => $a,
                'b' => $b,
            ];
        }
    }

    return $bar;
}

/** @return array{a: int, b: float}|null */
function foo2(int $a, float $b): ?array
{
    $bar = null;

    if ($a > 0) {
        $bar = [
            'a' => $a,
            'b' => $b,
        ];
    }

    return $bar;
}

/** @return array<array{a: int, b: float}>|null */
function foo3(int $a, float $b): ?array
{
    $bar = null;

    if ($a > 0) {
        for ($i = 0; $i < $a; $i++) {
            $bar[] = [
                'a' => $a,
                'b' => $b,
            ];
        }
    }

    return $bar;
}
