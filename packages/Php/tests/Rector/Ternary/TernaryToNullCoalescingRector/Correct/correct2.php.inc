<?php declare(strict_types=1);

$x = $a ?? 1;
$y = isset($b) ? "b" : 2;
$x = $c ?? 3;

$x = $a ?? $b ?? "";
