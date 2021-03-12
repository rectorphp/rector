<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class TruthyNegation
{
    public function accept()
    {
        return !$this->something() && !$this->somethingelse();
    }
}

?>
-----
<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

class TruthyNegation
{
    public function accept()
    {
        if ($this->something()) {
            return false;
        }
        return !$this->somethingelse();
    }
}

?>
