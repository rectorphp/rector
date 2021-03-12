<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\ArrayThisCallToThisMethodCallRector\Fixture;

class PrivateMethod
{
    public function run()
    {
        $values = [$this, 'giveMeMore'];
    }

    private function giveMeMore()
    {
        return 'more';
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\ArrayThisCallToThisMethodCallRector\Fixture;

class PrivateMethod
{
    public function run()
    {
        $values = $this->giveMeMore();
    }

    private function giveMeMore()
    {
        return 'more';
    }
}

?>
