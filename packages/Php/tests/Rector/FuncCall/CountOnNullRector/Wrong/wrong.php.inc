<?php declare(strict_types=1);

$values = null;

$valueCount = is_array($values) || $values instanceof Countable ? count($values) : 0;

$valueCount = count($values);
