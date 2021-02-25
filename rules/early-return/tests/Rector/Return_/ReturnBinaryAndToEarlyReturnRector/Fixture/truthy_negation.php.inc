<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

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

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryAndToEarlyReturnRector\Fixture;

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
