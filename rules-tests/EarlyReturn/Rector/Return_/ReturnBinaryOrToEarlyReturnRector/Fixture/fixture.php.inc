<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryOrToEarlyReturnRector\Fixture;

class Fixture
{
    public function accept()
    {
        return $this->something() || $this->somethingElse();
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryOrToEarlyReturnRector\Fixture;

class Fixture
{
    public function accept()
    {
        if ($this->something()) {
            return true;
        }
        return (bool) $this->somethingElse();
    }
}

?>
