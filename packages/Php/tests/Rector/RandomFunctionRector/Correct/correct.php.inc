<?php declare(strict_types=1);

random_int(0, mt_getrandmax());

mt_srand($a);

$a = &mt_srand($a);

/* foo */ mt_srand /** bar */ ($a);

a(mt_getrandmax ());

a(random_int(0, mt_getrandmax()));

a(mt_srand());

random_int($d, random_int($a,$b));

random_int($a, \Other\Scope\mt_rand($a));

$a = random_int(1,2) + random_int(3,4);
