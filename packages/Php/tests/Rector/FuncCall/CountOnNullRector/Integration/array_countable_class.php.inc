<?php

namespace Rector\Php\Tests\Rector\FuncCall\CountOnNullRector\Integration;

use Rector\Php\Tests\Rector\FuncCall\CountOnNullRector\Source\CountableClass;
use stdClass;

$values = [];
$count = count($values);

$values = new CountableClass();
$count = count($values);
