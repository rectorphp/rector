<?php declare(strict_types=1);

$values = null;

if ((is_array($values) || $values instanceof \Countable ? count($values) : 0) >= 2) {
    echo 'yes';
}
