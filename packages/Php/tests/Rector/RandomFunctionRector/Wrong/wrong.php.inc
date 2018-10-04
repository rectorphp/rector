<?php declare(strict_types=1);

rand();

srand($a);

$a = &srand($a);

/* foo */ srand /** bar */ ($a);

a(getrandmax ());

a(rand());

a(srand());

rand($d, rand($a,$b));

rand($a, \Other\Scope\mt_rand($a));

$a = rand(1,2) + mt_rand(3,4);
