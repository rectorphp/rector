<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryOrToEarlyReturnRector\Fixture;

class DontChangeAlreadyCasted
{
    public function accept()
    {
        return $this->something() || (bool) $this->somethingelse();
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryOrToEarlyReturnRector\Fixture;

class DontChangeAlreadyCasted
{
    public function accept()
    {
        if ($this->something()) {
            return true;
        }
        return (bool) $this->somethingelse();
    }
}

?>