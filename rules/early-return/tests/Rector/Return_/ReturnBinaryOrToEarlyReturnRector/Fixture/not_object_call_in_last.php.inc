<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryOrToEarlyReturnRector\Fixture;

class NotObjectCallInLast
{
    public function accept()
    {
        return $this->something() || true;
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryOrToEarlyReturnRector\Fixture;

class NotObjectCallInLast
{
    public function accept()
    {
        if ($this->something()) {
            return true;
        }
        return true;
    }
}

?>
