<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class MultipleBinaryAndStartWithMethodCall
{
    public function run($a, $b)
    {
        return $this->execute() && $a && $b;
    }

    private function execute() {}
}

?>
-----
<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class MultipleBinaryAndStartWithMethodCall
{
    public function run($a, $b)
    {
        if (!$this->execute()) {
            return false;
        }
        if (!$a) {
            return false;
        }
        return (bool) $b;
    }

    private function execute() {}
}

?>
