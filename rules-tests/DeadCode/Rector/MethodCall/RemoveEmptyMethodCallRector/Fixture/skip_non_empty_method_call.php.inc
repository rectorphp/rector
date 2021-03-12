<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

use DateTime;
use Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\Source\NonEmptyMethod;

$obj = new NonEmptyMethod(new DateTime('2000-01-01'));
$obj->run();

?>
