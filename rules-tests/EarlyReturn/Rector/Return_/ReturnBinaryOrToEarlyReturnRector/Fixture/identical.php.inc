<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector\Fixture;

class Identical
{
    public function accept()
    {
        return $this->something() === 1 || !$this->somethingElse();
    }
}

?>
-----
<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector\Fixture;

class Identical
{
    public function accept()
    {
        if ($this->something() === 1) {
            return true;
        }
        return !$this->somethingElse();
    }
}

?>
