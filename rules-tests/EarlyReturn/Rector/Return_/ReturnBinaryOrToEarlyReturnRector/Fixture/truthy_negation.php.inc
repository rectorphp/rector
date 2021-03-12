<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector\Fixture;

class TruthyNegation
{
    public function accept()
    {
        return !$this->something() || !$this->somethingelse();
    }
}

?>
-----
<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector\Fixture;

class TruthyNegation
{
    public function accept()
    {
        if (!$this->something()) {
            return true;
        }
        return !$this->somethingelse();
    }
}

?>
