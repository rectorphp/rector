<?php

namespace Rector\Tests\DowngradePhp55\Rector\Isset_\DowngradeArbitraryExpressionArgsToEmptyAndIssetRector\Fixture;

final class SomeClass
{
    public function run()
    {
        $object = new stdClass();
        return isset($object->foo); // must not changed
    }
}

?>
