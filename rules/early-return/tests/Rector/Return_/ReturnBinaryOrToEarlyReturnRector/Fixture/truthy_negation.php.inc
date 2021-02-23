<?php

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryOrToEarlyReturnRector\Fixture;

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

namespace Rector\EarlyReturn\Tests\Rector\Return_\ReturnBinaryOrToEarlyReturnRector\Fixture;

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
