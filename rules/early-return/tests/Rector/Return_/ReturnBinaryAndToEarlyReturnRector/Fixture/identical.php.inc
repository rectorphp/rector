<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class Identical
{
    public function accept()
    {
        return $this->something() === 1 && !$this->somethingElse();
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class Identical
{
    public function accept()
    {
        if ($this->something() !== 1) {
            return false;
        }
        return !$this->somethingElse();
    }
}

?>
