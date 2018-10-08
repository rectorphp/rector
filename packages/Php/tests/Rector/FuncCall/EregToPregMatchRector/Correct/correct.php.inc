<?php declare(strict_types=1);

preg_match('#hi#m', 'hi, she said');

preg_match('#hi#mi', 'hi, she said');

$pattern = 'hi';
preg_match('#' . preg_quote($pattern, '#') . '#m',  'hi, she said');
