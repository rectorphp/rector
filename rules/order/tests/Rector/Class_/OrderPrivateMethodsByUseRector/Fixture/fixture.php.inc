<?php

namespace Rector\Order\Tests\Rector\Class_\OrderPrivateMethodsByUseRector\Fixture;

class Fixture
{
    public function run()
    {
        $this->call1();
        $this->call2();
    }

    private function call2()
    {
    }

    private function call1()
    {
    }
}

?>
-----
<?php

namespace Rector\Order\Tests\Rector\Class_\OrderPrivateMethodsByUseRector\Fixture;

class Fixture
{
    public function run()
    {
        $this->call1();
        $this->call2();
    }

    private function call1()
    {
    }
    private function call2()
    {
    }
}

?>
