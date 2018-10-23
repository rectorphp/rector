<?php declare(strict_types=1);

$invalid = is_array($var) && in_array('foo', $var);
$almostValid = is_array($var) && count($var) > 0;
$invalid2 = isset($this->events[$event]) && ! empty($this->events[$event]);
$completelyInvalid = ! $value instanceof Foo && ! $value instanceof Bar;
