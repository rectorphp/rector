<?php declare(strict_types=1);

$x = isset($a) ? $a : null;

$x = isset($a[0]) ? $a[0] : 1;

$x=isset($a)?$a:null;

$x = isset   (  $a[$b["c"]]) ?$a[ $b[ "c"  ]]   : null;

$x = isset($a) ? $a : $b[func(1, true)];

$x = (isset($a) ? $a : isset($b)) ? $b : "";

$x = isset($a) ? $a : isset($b) ? $b : isset($c) ? $c : "";
