<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class NotObjectCallInLast
{
    public function accept()
    {
        return $this->something() && true;
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class NotObjectCallInLast
{
    public function accept()
    {
        if (!$this->something()) {
            return false;
        }
        return true;
    }
}

?>
