<?php declare(strict_types=1);

$x = isset($a) ? $a : 1;
$y = isset($b) ? "b" : 2;
$x = isset($c) ? $c : 3;

$x = isset($a) ? $a : (isset($b) ? $b : "");
