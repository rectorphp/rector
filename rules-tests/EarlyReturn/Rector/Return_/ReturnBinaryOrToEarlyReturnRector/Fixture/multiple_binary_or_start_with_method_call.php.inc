<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector\Fixture;

class MultipleBinaryOrStartWithMethodCall
{
    public function run($a, $b)
    {
        return $this->execute() || $a || $b;
    }

    private function execute() {}
}

?>
-----
<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector\Fixture;

class MultipleBinaryOrStartWithMethodCall
{
    public function run($a, $b)
    {
        if ($this->execute()) {
            return true;
        }
        if ($a) {
            return true;
        }
        return (bool) $b;
    }

    private function execute() {}
}

?>