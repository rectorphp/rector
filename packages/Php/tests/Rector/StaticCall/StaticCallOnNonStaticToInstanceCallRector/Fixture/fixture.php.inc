<?php

namespace Rector\Php\Tests\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Fixture;

class Something
{
    public function doWork()
    {
    }
}

class Another
{
    public function run()
    {
        return Something::doWork();
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector\Fixture;

class Something
{
    public function doWork()
    {
    }
}

class Another
{
    public function run()
    {
        return (new Something())->doWork();
    }
}

?>
