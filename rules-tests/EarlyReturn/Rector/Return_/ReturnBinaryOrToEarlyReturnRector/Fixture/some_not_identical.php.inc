<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector\Fixture;

final class SomeNotIdentical
{
    public function accept()
    {
        return $this->something() !== 1 || !$this->somethingelse();
    }
}

?>
-----
<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector\Fixture;

final class SomeNotIdentical
{
    public function accept()
    {
        if ($this->something() !== 1) {
            return true;
        }
        return !$this->somethingelse();
    }
}

?>
