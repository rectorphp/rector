<?php declare(strict_types=1);

is_iterable($foo);

is_string($foo) || $foo instanceof Traversable;

is_array($foo2) || $foo instanceof Traversable;

$foo2 || $foo instanceof Traversable;
