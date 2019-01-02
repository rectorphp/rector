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
        return Something::doWork(1);
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
        return (new Something())->doWork(1);
    }
}

?>
