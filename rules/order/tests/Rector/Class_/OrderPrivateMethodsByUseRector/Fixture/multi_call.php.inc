<?php

namespace Rector\Order\Tests\Rector\Class_\OrderPrivateMethodsByUseRector\Fixture;

class MultiCall
{
    public function run()
    {
        $this->call3();
    }

    private function call2()
    {
    }

    private function call1()
    {
    }

    private function call3()
    {
        $this->call1();
        $this->call2();
    }
}

?>
-----
<?php

namespace Rector\Order\Tests\Rector\Class_\OrderPrivateMethodsByUseRector\Fixture;

class MultiCall
{
    public function run()
    {
        $this->call3();
    }

    private function call3()
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
