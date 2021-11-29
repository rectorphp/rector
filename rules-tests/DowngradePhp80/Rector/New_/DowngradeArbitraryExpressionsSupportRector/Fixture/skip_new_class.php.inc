<?php

namespace Rector\Tests\DowngradePhp80\Rector\New_\DowngradeArbitraryExpressionsSupportRector\Fixture;

$object = new \stdClass();

$class = \stdClass::class;

var_dump($object instanceof $class);

?>
