<?php

namespace Rector\Tests\DowngradePhp70\Rector\MethodCall\DowngradeMethodCallOnCloneRector\Fixture;

class AssignInsidevar
{
    public function run()
    {
        ($object = $object2 = clone $this)->execute();
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

class AssignInsidevar
{
    public function run()
    {
        $object = $object2 = clone $this;
        $object->execute();
    }

    public function execute()
    {
        echo 'test';
    }
}

?>
