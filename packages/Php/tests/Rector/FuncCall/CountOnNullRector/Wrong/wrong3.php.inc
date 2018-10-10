<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FuncCall\CountOnNullRector\Wrong;

use stdClass;

$values = false;
$count = count($values);

$values = true;
$count = count($values);

$values = new stdClass();
$count = count($values);
