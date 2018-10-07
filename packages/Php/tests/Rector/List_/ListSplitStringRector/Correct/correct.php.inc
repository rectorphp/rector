<?php declare(strict_types=1);

$string = 'abcde';
list($foo) = str_split($string);

$array = ['abcde'];
list($bar) = $array;

list($foo) = str_split('abcde');
