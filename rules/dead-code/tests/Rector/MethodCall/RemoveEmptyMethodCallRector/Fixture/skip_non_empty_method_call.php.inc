<?php

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

use DateTime;
use Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Source\NonEmptyMethod;

$obj = new NonEmptyMethod(new DateTime('2000-01-01'));
$obj->run();

?>
