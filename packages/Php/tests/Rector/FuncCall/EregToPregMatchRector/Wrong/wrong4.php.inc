<?php declare(strict_types=1);

$result = ereg('hitom', '...hitom...', $matches);
var_dump($result); // 5

// no return value
ereg('hitom', '...hitom...', $matches);
