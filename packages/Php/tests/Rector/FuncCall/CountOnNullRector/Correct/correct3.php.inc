<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\CountOnNullRector\Wrong;

use stdClass;

$values = false;
$count = is_array($values) || $values instanceof \Countable ? count($values) : 0;

$values = true;
$count = is_array($values) || $values instanceof \Countable ? count($values) : 0;

$values = new stdClass();
$count = is_array($values) || $values instanceof \Countable ? count($values) : 0;
