<?php

namespace Rector\Tests\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector\Fixture;

function skipDoWhile(array $array): array
{
    if ($array === []) {
        return [];
    }

    $new = [];

    do {
        array_splice($array, 0, 1);
        $new[] = strtoupper(current($array));
    } while (count($array) > 0);

    return $new;
}

?>
