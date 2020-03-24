<?php

namespace Rector\Php71\Tests\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

function each(array $array, callable $fn): array
{
    foreach ($array as $key => $value) {
        if ($fn($value, $key) === false) {
            break;
        }
    }
    return $array;
}

function eachSpread(array $array, callable $fn): array
{
    return each($array, static function (array $chunk, $key) use ($fn) {
        $chunk[] = $key;

        return $fn(...$chunk);
    });
}
