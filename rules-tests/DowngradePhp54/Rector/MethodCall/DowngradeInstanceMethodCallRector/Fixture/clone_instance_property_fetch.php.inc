<?php

namespace Rector\Tests\DowngradePhp54\Rector\MethodCall\DowngradeInstanceMethodCallRector\Fixture;

final class SomeClass
{
    public $name = 'foo';

    public function run()
    {
        return (clone $this)->name;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp54\Rector\MethodCall\DowngradeInstanceMethodCallRector\Fixture;

final class SomeClass
{
    public $name = 'foo';

    public function run()
    {
        $object = clone $this;
        return $object->name;
    }
}

?>
