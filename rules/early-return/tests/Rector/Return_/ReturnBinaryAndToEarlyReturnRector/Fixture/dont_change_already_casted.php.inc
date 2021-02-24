<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class DontChangeAlreadyCasted
{
    public function accept()
    {
        return $this->something() && (bool) $this->somethingelse();
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class DontChangeAlreadyCasted
{
    public function accept()
    {
        if (!$this->something()) {
            return false;
        }
        return (bool) $this->somethingelse();
    }
}

?>