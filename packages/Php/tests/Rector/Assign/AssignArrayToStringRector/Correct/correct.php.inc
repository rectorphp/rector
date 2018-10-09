<?php declare(strict_types=1);

$string = [];
$string[] = 1;

$string2 = [];
$someRubishInBetween = 1;
$string2[] = 1;

$string3 = $string;
$string3 = (array) $string3;
$string3[] = 1;
