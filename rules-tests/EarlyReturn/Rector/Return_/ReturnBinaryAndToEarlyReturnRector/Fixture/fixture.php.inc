<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class Fixture
{
    public function accept()
    {
        return $this->something() && $this->somethingElse();
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class Fixture
{
    public function accept()
    {
        if (!$this->something()) {
            return false;
        }
        return (bool) $this->somethingElse();
    }
}

?>
