<?php

namespace Rector\Tests\DowngradePhp70\Rector\MethodCall\DowngradeMethodCallOnCloneRector\Fixture;

class Fixture
{
    public function run()
    {
        (clone $this)->execute();
    }

    public function execute()
    {
        echo 'test';
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\MethodCall\DowngradeMethodCallOnCloneRector\Fixture;

class Fixture
{
    public function run()
    {
        $object = clone $this;
        $object->execute();
    }

    public function execute()
    {
        echo 'test';
    }
}

?>
