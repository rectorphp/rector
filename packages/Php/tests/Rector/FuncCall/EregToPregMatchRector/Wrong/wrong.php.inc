<?php declare(strict_types=1);

ereg('hi', 'hi, she said');

eregi('hi', 'hi, she said');

$pattern = 'hi';
ereg($pattern,  'hi, she said');
