<?php

namespace Rector\Tests\DowngradePhp54\Rector\MethodCall\DowngradeInstanceMethodCallRector\Fixture;

final class SomeClass
{
    public function run()
    {
        return (new \ReflectionClass('\\stdClass'))->name;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp54\Rector\MethodCall\DowngradeInstanceMethodCallRector\Fixture;

final class SomeClass
{
    public function run()
    {
        $object = new \ReflectionClass('\\stdClass');
        return $object->name;
    }
}

?>
