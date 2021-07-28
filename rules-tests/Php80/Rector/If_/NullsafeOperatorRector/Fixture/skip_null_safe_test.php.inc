<?php declare(strict_types = 1);

function test(?string $argument): void
{
    /** @var string[] $array */
    $array = [];

    if ($argument !== null) {
        $array[] = $argument;
    }
}
