<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector\Fixture;

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

namespace Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector\Fixture;

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
