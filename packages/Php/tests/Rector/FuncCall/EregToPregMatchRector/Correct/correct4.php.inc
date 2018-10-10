<?php declare(strict_types=1);

$result = preg_match('#hitom#m', '...hitom...', $matches) ? strlen($matches[0]) : false;
var_dump($result); // 5

// no return value
preg_match('#hitom#m', '...hitom...', $matches);
