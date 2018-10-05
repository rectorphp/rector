<?php declare(strict_types=1);

$x = $a ?? null;

$x = $a[0] ?? 1;

$x=$a ?? null;

$x = $a[ $b[ "c"  ]] ?? null;

$x = $a ?? $b[func(1, true)];

$x = ($a ?? isset($b)) ? $b : "";

$x = $a ?? isset($b) ? $b : isset($c) ? $c : "";
